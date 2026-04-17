import { NextRequest, NextResponse } from 'next/server';

// Org-scoped paths: /[orgSlug]/manage/*, /[orgSlug]/portal/*
// Main protected paths: /admin/*
const PROTECTED_PREFIX = ['/admin'];

/** Returns the org slug from a path like /river/login, /river/manage, /river/portal */
function getOrgSlugFromPathname(pathname: string): string | null {
  const parts = pathname.split('/').filter(Boolean);
  if (parts.length >= 2 && ['login', 'portal', 'manage'].includes(parts[1])) {
    return parts[0];
  }
  return null;
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const token = request.cookies.get('membix_token')?.value
    ?? request.headers.get('x-membix-token');

  const hasSession = Boolean(token);
  const orgSlug = getOrgSlugFromPathname(pathname);

  const isMainAuthPath = ['/login', '/register'].some((p) => pathname === p || pathname.startsWith(p + '/'));
  const isProtectedPath = PROTECTED_PREFIX.some((p) => pathname === p || pathname.startsWith(p + '/'));

  // Protect /admin/* routes
  if (isProtectedPath && !hasSession) {
    const loginUrl = new URL('/login', request.url);
    loginUrl.searchParams.set('redirect', pathname);
    return NextResponse.redirect(loginUrl);
  }

  // Protect org-scoped /[orgSlug]/manage/* and /[orgSlug]/portal/* routes
  if (orgSlug) {
    const isOrgProtected = pathname.includes('/manage') || pathname.includes('/portal');
    if (isOrgProtected && !hasSession) {
      const loginUrl = new URL(`/${orgSlug}/login`, request.url);
      loginUrl.searchParams.set('redirect', pathname);
      return NextResponse.redirect(loginUrl);
    }
  }

  // Redirect authenticated users away from main /login and /register
  if (isMainAuthPath && hasSession) {
    return NextResponse.redirect(new URL('/admin/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    '/((?!_next/static|_next/image|favicon.ico|robots.txt).*)',
  ],
};

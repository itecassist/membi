import { NextRequest, NextResponse } from 'next/server';

const PUBLIC_PATHS = ['/', '/login', '/register', '/pricing', '/features', '/help', '/about', '/contact', '/privacy', '/terms', '/docs', '/support'];
const PROTECTED_PREFIX = ['/admin', '/manage', '/portal'];

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const token = request.cookies.get('membix_token')?.value
    ?? request.headers.get('x-membix-token');

  // Check localStorage-based token via a cookie we mirror on login
  // Next.js middleware can't access localStorage, so we use a non-httpOnly cookie
  // as a session indicator (actual Bearer token stays in localStorage)
  const hasSession = Boolean(token);

  const isPublicPath = PUBLIC_PATHS.some((p) => pathname === p || pathname.startsWith(p + '/'));
  const isAuthOnlyPath = ['/login', '/register'].some((p) => pathname === p || pathname.startsWith(p + '/'));
  const isProtectedPath = PROTECTED_PREFIX.some((p) => pathname === p || pathname.startsWith(p + '/'));

  // Redirect unauthenticated users away from protected routes
  if (isProtectedPath && !hasSession) {
    const loginUrl = new URL('/login', request.url);
    loginUrl.searchParams.set('redirect', pathname);
    return NextResponse.redirect(loginUrl);
  }

  // Redirect authenticated users away from login/register
  if (isAuthOnlyPath && hasSession) {
    return NextResponse.redirect(new URL('/admin/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    '/((?!_next/static|_next/image|favicon.ico|robots.txt).*)',
  ],
};

import { NextRequest, NextResponse } from 'next/server';
import { extractSubdomainFromHost } from '@/lib/subdomain';

const PUBLIC_PATHS = ['/', '/login', '/register', '/pricing', '/features', '/help', '/about', '/contact', '/privacy', '/terms', '/docs', '/support'];
const PROTECTED_PREFIX = ['/admin', '/manage', '/portal'];

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const host = request.headers.get('host') || '';
  const token = request.cookies.get('membix_token')?.value
    ?? request.headers.get('x-membix-token');

  const hasSession = Boolean(token);
  const isOnSubdomain = Boolean(extractSubdomainFromHost(host));

  const isPublicPath = PUBLIC_PATHS.some((p) => pathname === p || pathname.startsWith(p + '/'));
  const isAuthOnlyPath = ['/login', '/register'].some((p) => pathname === p || pathname.startsWith(p + '/'));
  const isProtectedPath = PROTECTED_PREFIX.some((p) => pathname === p || pathname.startsWith(p + '/'));

  // Redirect unauthenticated users away from protected routes
  if (isProtectedPath && !hasSession) {
    const loginUrl = new URL('/login', request.url);
    loginUrl.searchParams.set('redirect', pathname);
    return NextResponse.redirect(loginUrl);
  }

  // On main domain: redirect authenticated users away from login/register → admin dashboard
  // On org subdomain: skip this redirect — let the login page handle post-auth navigation
  if (isAuthOnlyPath && hasSession && !isOnSubdomain) {
    return NextResponse.redirect(new URL('/admin/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    '/((?!_next/static|_next/image|favicon.ico|robots.txt).*)',
  ],
};

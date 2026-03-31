/**
 * Extracts the org subdomain from the current browser hostname.
 *
 * Examples:
 *   localhost           → null       (main domain)
 *   river.localhost     → "river"    (org subdomain)
 *   river.membix.com    → "river"    (org subdomain, prod)
 */
export function getOrgSubdomain(): string | null {
  if (typeof window === 'undefined') return null;

  const hostname = window.location.hostname;
  const parts = hostname.split('.');

  // "river.localhost" → 2 parts, last is "localhost"
  if (parts.length === 2 && parts[1] === 'localhost') {
    return parts[0];
  }

  // "river.membix.com" → 3+ parts
  if (parts.length >= 3) {
    return parts[0];
  }

  return null;
}

/**
 * Builds the backend API base URL for an org subdomain.
 * e.g. "river" → "http://river.localhost:8000/api"
 */
export function getOrgApiBaseUrl(subdomain: string): string {
  const apiDomain = process.env.NEXT_PUBLIC_API_DOMAIN || 'localhost:8000';
  return `http://${subdomain}.${apiDomain}/api`;
}

/**
 * Builds the frontend URL for an org subdomain.
 * e.g. "river" → "http://river.localhost:3000"
 */
export function getOrgFrontendUrl(subdomain: string): string {
  const appDomain = process.env.NEXT_PUBLIC_APP_DOMAIN || 'localhost:3000';
  return `http://${subdomain}.${appDomain}`;
}

/**
 * Extracts the subdomain from a hostname string (for use in middleware / server code).
 * e.g. "river.localhost:3000" with appDomain "localhost:3000" → "river"
 */
export function extractSubdomainFromHost(host: string): string | null {
  // Strip port
  const hostname = host.split(':')[0];
  const parts = hostname.split('.');

  if (parts.length === 2 && parts[1] === 'localhost') {
    return parts[0];
  }
  if (parts.length >= 3) {
    return parts[0];
  }
  return null;
}

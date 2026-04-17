/**
 * Extracts the org slug from the current URL path.
 *
 * The slug is the first path segment when the second segment is a known
 * org-scoped route (login, portal, manage).
 *
 * Examples:
 *   /river/login   → "river"
 *   /river/portal  → "river"
 *   /river/manage  → "river"
 *   /login         → null  (main domain)
 *   /admin/...     → null  (platform admin)
 */
export function getOrgSlugFromPath(): string | null {
  if (typeof window === 'undefined') return null;

  const parts = window.location.pathname.split('/').filter(Boolean);
  if (parts.length >= 2 && ['login', 'portal', 'manage'].includes(parts[1])) {
    return parts[0];
  }
  return null;
}

/**
 * Builds the backend API base URL for an org slug.
 * e.g. "river" → "http://localhost/api/river"
 */
export function getOrgApiBaseUrl(slug: string): string {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost/api';
  return `${apiUrl}/${slug}`;
}

/**
 * Builds the frontend URL for an org slug.
 * e.g. "river" → "http://localhost/river"
 */
export function getOrgFrontendUrl(slug: string): string {
  const mainUrl = process.env.NEXT_PUBLIC_MAIN_URL || 'http://localhost';
  return `${mainUrl}/${slug}`;
}

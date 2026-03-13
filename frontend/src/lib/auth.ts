const TOKEN_KEY = 'membix_token';

export const getToken = (): string | null => {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(TOKEN_KEY);
};

export const setToken = (token: string): void => {
  localStorage.setItem(TOKEN_KEY, token);
  // Mirror a non-httpOnly session indicator cookie so Next.js middleware can gate routes.
  // The actual Bearer token remains in localStorage only.
  document.cookie = `${TOKEN_KEY}=1; path=/; SameSite=Lax`;
};

export const clearToken = (): void => {
  localStorage.removeItem(TOKEN_KEY);
  document.cookie = `${TOKEN_KEY}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
};

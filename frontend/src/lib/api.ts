import axios, { type AxiosInstance } from 'axios';
import { getToken, clearToken } from '@/lib/auth';

function attachInterceptors(instance: AxiosInstance): AxiosInstance {
  instance.interceptors.request.use((config) => {
    const token = getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  });

  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        clearToken();
        if (typeof window !== 'undefined') {
          window.location.href = '/login';
        }
      }
      return Promise.reject(error);
    }
  );

  return instance;
}

const api = attachInterceptors(
  axios.create({
    baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })
);

/**
 * Creates an Axios instance scoped to an org's subdomain backend.
 * e.g. baseUrl = "http://river.localhost:8000/api"
 */
export function createOrgApiClient(baseUrl: string): AxiosInstance {
  return attachInterceptors(
    axios.create({
      baseURL: baseUrl,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    })
  );
}

export default api;

import { create } from 'zustand';
import api, { createOrgApiClient } from '@/lib/api';
import { getToken, setToken, clearToken } from '@/lib/auth';
import { getOrgSlugFromPath, getOrgApiBaseUrl } from '@/lib/subdomain';
import type { User, Organisation, LoginPayload, RegisterPayload } from '@/types/api';

interface AuthState {
  user: User | null;
  token: string | null;
  organisations: Organisation[];
  currentOrganisation: Organisation | null;

  // Actions
  login: (payload: LoginPayload) => Promise<void>;
  register: (payload: RegisterPayload) => Promise<void>;
  logout: () => Promise<void>;
  loadUser: () => Promise<void>;
  loadOrganisations: () => Promise<void>;
  setCurrentOrganisation: (org: Organisation) => void;
}

/** Returns an org-scoped client when on an org path, otherwise the main API client. */
function getClient() {
  const slug = getOrgSlugFromPath();
  return slug ? createOrgApiClient(getOrgApiBaseUrl(slug)) : api;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: getToken(),
  organisations: [],
  currentOrganisation: null,

  login: async (payload) => {
    const client = getClient();
    const { data } = await client.post<{ data: User; token: string }>('/auth/login', payload);
    setToken(data.token);
    set({ token: data.token, user: data.data });
    await get().loadOrganisations();
  },

  register: async (payload) => {
    const client = getClient();
    const { data } = await client.post<{ data: User; token: string }>('/auth/register', payload);
    setToken(data.token);
    set({ token: data.token, user: data.data });
    await get().loadOrganisations();
  },

  logout: async () => {
    const client = getClient();
    try {
      await client.post('/auth/logout');
    } finally {
      clearToken();
      set({ user: null, token: null, organisations: [], currentOrganisation: null });
    }
  },

  loadUser: async () => {
    const client = getClient();
    const { data } = await client.get<{ data: User }>('/auth/me');
    set({ user: data.data });
  },

  loadOrganisations: async () => {
    const client = getClient();
    const { data } = await client.get<{ data: { organisation: Organisation; member: unknown }[] }>('/auth/organisations');
    const orgs = data.data.map((item) => item.organisation);
    set({
      organisations: orgs,
      currentOrganisation: orgs.length > 0 ? orgs[0] : null,
    });
  },

  setCurrentOrganisation: (org) => {
    set({ currentOrganisation: org });
  },
}));

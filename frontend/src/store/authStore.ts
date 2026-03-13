import { create } from 'zustand';
import api from '@/lib/api';
import { getToken, setToken, clearToken } from '@/lib/auth';
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

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: getToken(),
  organisations: [],
  currentOrganisation: null,

  login: async (payload) => {
    const { data } = await api.post<{ data: User; token: string }>('/auth/login', payload);
    setToken(data.token);
    set({ token: data.token, user: data.data });
    await get().loadOrganisations();
  },

  register: async (payload) => {
    const { data } = await api.post<{ data: User; token: string }>('/auth/register', payload);
    setToken(data.token);
    set({ token: data.token, user: data.data });
    await get().loadOrganisations();
  },

  logout: async () => {
    try {
      await api.post('/auth/logout');
    } finally {
      clearToken();
      set({ user: null, token: null, organisations: [], currentOrganisation: null });
    }
  },

  loadUser: async () => {
    const { data } = await api.get<{ data: User }>('/auth/me');
    set({ user: data.data });
  },

  loadOrganisations: async () => {
    const { data } = await api.get<{ data: { organisation: Organisation; member: unknown }[] }>('/auth/organisations');
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

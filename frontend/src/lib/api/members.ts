import api from '@/lib/api';
import type {
  Member,
  StoreMemberPayload,
  UpdateMemberPayload,
  PaginatedResponse,
  ApiResponse,
} from '@/types/api';

export const membersApi = {
  list: (orgSlug: string, page = 1) =>
    api.get<PaginatedResponse<Member>>(`/${orgSlug}/members`, { params: { page } }),

  get: (orgSlug: string, memberId: string) =>
    api.get<ApiResponse<Member>>(`/${orgSlug}/members/${memberId}`),

  create: (orgSlug: string, data: StoreMemberPayload) =>
    api.post<ApiResponse<Member>>(`/${orgSlug}/members`, data),

  update: (orgSlug: string, memberId: string, data: UpdateMemberPayload) =>
    api.put<ApiResponse<Member>>(`/${orgSlug}/members/${memberId}`, data),

  delete: (orgSlug: string, memberId: string) =>
    api.delete(`/${orgSlug}/members/${memberId}`),
};

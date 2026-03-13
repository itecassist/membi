import api from '@/lib/api';
import type {
  Member,
  StoreMemberPayload,
  UpdateMemberPayload,
  PaginatedResponse,
  ApiResponse,
} from '@/types/api';

export const membersApi = {
  list: (orgId: string, page = 1) =>
    api.get<PaginatedResponse<Member>>(`/organisations/${orgId}/members`, { params: { page } }),

  get: (orgId: string, memberId: string) =>
    api.get<ApiResponse<Member>>(`/organisations/${orgId}/members/${memberId}`),

  create: (orgId: string, data: StoreMemberPayload) =>
    api.post<ApiResponse<Member>>(`/organisations/${orgId}/members`, data),

  update: (orgId: string, memberId: string, data: UpdateMemberPayload) =>
    api.put<ApiResponse<Member>>(`/organisations/${orgId}/members/${memberId}`, data),

  delete: (orgId: string, memberId: string) =>
    api.delete(`/organisations/${orgId}/members/${memberId}`),
};

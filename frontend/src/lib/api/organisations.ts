import api from '@/lib/api';
import type {
  Organisation,
  StoreOrganisationPayload,
  UpdateOrganisationPayload,
  PaginatedResponse,
  ApiResponse,
} from '@/types/api';

export const organisationsApi = {
  list: (page = 1) =>
    api.get<PaginatedResponse<Organisation>>('/organisations', { params: { page } }),

  get: (id: string) =>
    api.get<ApiResponse<Organisation>>(`/organisations/${id}`),

  create: (data: StoreOrganisationPayload) =>
    api.post<ApiResponse<Organisation>>('/organisations', data),

  update: (id: string, data: UpdateOrganisationPayload) =>
    api.put<ApiResponse<Organisation>>(`/organisations/${id}`, data),

  delete: (id: string) =>
    api.delete(`/organisations/${id}`),
};

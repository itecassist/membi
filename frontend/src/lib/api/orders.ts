import api from '@/lib/api';
import type { ApiResponse, PaginatedResponse, Order } from '@/types/api';

export const ordersApi = {
  list: (orgId: string, page = 1) =>
    api.get<PaginatedResponse<Order>>(`/organisations/${orgId}/orders`, { params: { page } }),

  get: (orgId: string, orderId: string) =>
    api.get<ApiResponse<Order>>(`/organisations/${orgId}/orders/${orderId}`),
};

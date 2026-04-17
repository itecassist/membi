import api from '@/lib/api';
import type { ApiResponse, PaginatedResponse, Order } from '@/types/api';

export const ordersApi = {
  list: (orgSlug: string, page = 1) =>
    api.get<PaginatedResponse<Order>>(`/${orgSlug}/orders`, { params: { page } }),

  get: (orgSlug: string, orderId: string) =>
    api.get<ApiResponse<Order>>(`/${orgSlug}/orders/${orderId}`),
};

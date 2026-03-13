import api from '@/lib/api';
import type {
  ApiResponse,
  PaginatedResponse,
  Subscription,
  SubscriptionPriceOption,
  SubscriptionStats,
  StoreSubscriptionPayload,
  StoreSubscriptionPriceOptionPayload,
} from '@/types/api';

export const subscriptionsApi = {
  list: (orgId: string) =>
    api.get<{ data: Subscription[] }>(`/organisations/${orgId}/subscriptions`),

  stats: (orgId: string) =>
    api.get<ApiResponse<SubscriptionStats>>(`/organisations/${orgId}/subscriptions/stats`),

  get: (orgId: string, subId: string) =>
    api.get<ApiResponse<Subscription>>(`/organisations/${orgId}/subscriptions/${subId}`),

  create: (orgId: string, data: StoreSubscriptionPayload) =>
    api.post<ApiResponse<Subscription>>(`/organisations/${orgId}/subscriptions`, data),

  update: (orgId: string, subId: string, data: Partial<StoreSubscriptionPayload>) =>
    api.put<ApiResponse<Subscription>>(`/organisations/${orgId}/subscriptions/${subId}`, data),

  delete: (orgId: string, subId: string) =>
    api.delete(`/organisations/${orgId}/subscriptions/${subId}`),

  // Price options
  priceOptions: (orgId: string, subId: string) =>
    api.get<{ data: SubscriptionPriceOption[] }>(
      `/organisations/${orgId}/subscriptions/${subId}/price-options`
    ),

  addPriceOption: (orgId: string, subId: string, data: StoreSubscriptionPriceOptionPayload) =>
    api.post<ApiResponse<SubscriptionPriceOption>>(
      `/organisations/${orgId}/subscriptions/${subId}/price-options`,
      data
    ),

  updatePriceOption: (
    orgId: string,
    subId: string,
    optionId: string,
    data: Partial<StoreSubscriptionPriceOptionPayload>
  ) =>
    api.put<ApiResponse<SubscriptionPriceOption>>(
      `/organisations/${orgId}/subscriptions/${subId}/price-options/${optionId}`,
      data
    ),

  deletePriceOption: (orgId: string, subId: string, optionId: string) =>
    api.delete(`/organisations/${orgId}/subscriptions/${subId}/price-options/${optionId}`),
};

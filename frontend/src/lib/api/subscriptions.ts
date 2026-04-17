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
  list: (orgSlug: string) =>
    api.get<{ data: Subscription[] }>(`/${orgSlug}/subscriptions`),

  stats: (orgSlug: string) =>
    api.get<ApiResponse<SubscriptionStats>>(`/${orgSlug}/subscriptions/stats`),

  get: (orgSlug: string, subId: string) =>
    api.get<ApiResponse<Subscription>>(`/${orgSlug}/subscriptions/${subId}`),

  create: (orgSlug: string, data: StoreSubscriptionPayload) =>
    api.post<ApiResponse<Subscription>>(`/${orgSlug}/subscriptions`, data),

  update: (orgSlug: string, subId: string, data: Partial<StoreSubscriptionPayload>) =>
    api.put<ApiResponse<Subscription>>(`/${orgSlug}/subscriptions/${subId}`, data),

  delete: (orgSlug: string, subId: string) =>
    api.delete(`/${orgSlug}/subscriptions/${subId}`),

  // Price options
  priceOptions: (orgSlug: string, subId: string) =>
    api.get<{ data: SubscriptionPriceOption[] }>(
      `/${orgSlug}/subscriptions/${subId}/price-options`
    ),

  addPriceOption: (orgSlug: string, subId: string, data: StoreSubscriptionPriceOptionPayload) =>
    api.post<ApiResponse<SubscriptionPriceOption>>(
      `/${orgSlug}/subscriptions/${subId}/price-options`,
      data
    ),

  updatePriceOption: (
    orgSlug: string,
    subId: string,
    optionId: string,
    data: Partial<StoreSubscriptionPriceOptionPayload>
  ) =>
    api.put<ApiResponse<SubscriptionPriceOption>>(
      `/${orgSlug}/subscriptions/${subId}/price-options/${optionId}`,
      data
    ),

  deletePriceOption: (orgSlug: string, subId: string, optionId: string) =>
    api.delete(`/${orgSlug}/subscriptions/${subId}/price-options/${optionId}`),
};

import apiClient from './client';
import {Property, PaginatedResponse} from '../types';

export interface PropertyFilters {
  city?: string;
  type?: string;
  check_in?: string;
  check_out?: string;
  price_min?: number;
  price_max?: number;
  guests?: number;
  sort?: string;
  page?: number;
}

export const propertiesApi = {
  list: (filters?: PropertyFilters) =>
    apiClient.get<PaginatedResponse<Property>>('/properties', {params: filters}),

  featured: () =>
    apiClient.get<{data: Property[]}>('/properties/featured'),

  show: (id: number) =>
    apiClient.get<{data: Property}>(`/properties/${id}`),
};

import apiClient from './client';
import {Property} from '../types';

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

interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

interface PropertiesListData {
  properties: Property[];
  pagination: Pagination;
}

export const propertiesApi = {
  list: (filters?: PropertyFilters) =>
    apiClient.get<{success: boolean; data: PropertiesListData}>('/properties', {params: filters}),

  featured: () =>
    apiClient.get<{success: boolean; data: Property[]}>('/properties/featured'),

  show: (id: number) =>
    apiClient.get<{success: boolean; data: Property}>(`/properties/${id}`),
};

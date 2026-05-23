import apiClient from './client';
import {Property} from '../types';

interface OwnerPropertiesData {
  properties: Property[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

interface ToggleStatusResponse {
  success: boolean;
  message: string;
  kyc_required?: boolean;
  kyc_status?: string;
  data?: {status: string};
}

export const ownerPropertiesApi = {
  list: (page = 1) =>
    apiClient.get<{success: boolean; data: OwnerPropertiesData}>('/owner/properties', {
      params: {page},
    }),

  show: (id: number) =>
    apiClient.get<{success: boolean; data: Property}>(`/owner/properties/${id}`),

  store: (formData: FormData) =>
    apiClient.post<{success: boolean; message: string; data: Property}>(
      '/owner/properties',
      formData,
      {headers: {'Content-Type': 'multipart/form-data'}},
    ),

  toggleStatus: (id: number) =>
    apiClient.post<ToggleStatusResponse>(`/owner/properties/${id}/toggle-status`),

  destroy: (id: number) =>
    apiClient.delete<{success: boolean; message: string}>(`/owner/properties/${id}`),
};

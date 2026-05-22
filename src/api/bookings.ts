import apiClient from './client';
import {Booking} from '../types';

export interface CreateBookingPayload {
  property_id: number;
  check_in: string;
  check_out: string;
  guests: number;
  special_requests?: string;
}

interface Pagination {
  current_page: number;
  last_page: number;
  total: number;
}

interface BookingsListData {
  bookings: Booking[];
  pagination: Pagination;
}

export const bookingsApi = {
  list: () =>
    apiClient.get<{success: boolean; data: BookingsListData}>('/bookings'),

  show: (id: number) =>
    apiClient.get<{success: boolean; data: Booking}>(`/bookings/${id}`),

  create: (payload: CreateBookingPayload) =>
    apiClient.post<{success: boolean; message: string; data: Booking}>('/bookings', payload),

  cancel: (id: number) =>
    apiClient.post<{success: boolean; message: string}>(`/bookings/${id}/cancel`),
};

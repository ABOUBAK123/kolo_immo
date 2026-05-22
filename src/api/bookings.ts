import apiClient from './client';
import {Booking, PaginatedResponse} from '../types';

export interface CreateBookingPayload {
  property_id: number;
  check_in: string;
  check_out: string;
  guests: number;
  special_requests?: string;
}

export const bookingsApi = {
  list: () =>
    apiClient.get<PaginatedResponse<Booking>>('/bookings'),

  show: (id: number) =>
    apiClient.get<{data: Booking}>(`/bookings/${id}`),

  create: (payload: CreateBookingPayload) =>
    apiClient.post<{data: Booking; message: string}>('/bookings', payload),

  cancel: (id: number) =>
    apiClient.post<{message: string}>(`/bookings/${id}/cancel`),
};

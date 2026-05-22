import apiClient from './client';

export interface InitiatePaymentPayload {
  phone_number: string;
  method: string;
}

export const paymentsApi = {
  initiate: (bookingId: number, payload: InitiatePaymentPayload) =>
    apiClient.post(`/payments/initiate/${bookingId}`, payload),
};

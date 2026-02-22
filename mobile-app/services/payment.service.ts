import { apiClient, API_ENDPOINTS } from '../api';

export interface PaymentRequest {
  order_id: string;
  amount: number;
  payment_method?: 'credit_card' | 'bank_transfer' | 'gopay' | 'shopeepay';
  customer_details?: {
    first_name: string;
    email: string;
    phone: string;
  };
}

export interface PaymentResponse {
  snap_token: string;
  payment_url: string;
  transaction_id: string;
}

export interface PaymentStatus {
  transaction_id: string;
  transaction_status: 'pending' | 'settlement' | 'capture' | 'deny' | 'expire' | 'cancel' | 'refund' | 'chargeback';
  fraud_status: 'accept' | 'deny' | 'challenge';
  payment_type: string;
  gross_amount: string;
  transaction_time: string;
  status_message: string;
}

/**
 * Initialize Midtrans Snap payment
 */
export async function initiatePayment(request: PaymentRequest): Promise<PaymentResponse> {
  try {
    const response = await apiClient.post(API_ENDPOINTS.PAYMENTS_INITIATE, request);
    return response.data || response;
  } catch (error) {
    console.error('Error initiating payment:', error);
    throw error;
  }
}

/**
 * Get payment status
 */
export async function getPaymentStatus(orderId: string): Promise<PaymentStatus> {
  try {
    const response = await apiClient.get(API_ENDPOINTS.PAYMENTS_STATUS.replace('{orderNumber}', orderId));
    return response.data || response;
  } catch (error) {
    console.error('Error getting payment status:', error);
    throw error;
  }
}

/**
 * Cancel payment
 */
export async function cancelPayment(orderId: string): Promise<boolean> {
  try {
    const response = await apiClient.post(API_ENDPOINTS.PAYMENTS_CANCEL, {
      invoice_number: orderId,
    });
    return response.success || false;
  } catch (error) {
    console.error('Error canceling payment:', error);
    return false;
  }
}

/**
 * Poll payment status until final state
 */
export async function pollPaymentStatus(
  orderId: string,
  maxAttempts: number = 60,
  intervalMs: number = 2000
): Promise<PaymentStatus> {
  let attempts = 0;
  
  while (attempts < maxAttempts) {
    try {
      const status = await getPaymentStatus(orderId);
      
      // Check if payment is in final state
      const finalStates = ['settlement', 'capture', 'deny', 'expire', 'cancel', 'chargeback'];
      if (finalStates.includes(status.transaction_status)) {
        return status;
      }
      
      // Wait before next poll
      await new Promise(resolve => setTimeout(resolve, intervalMs));
      attempts++;
    } catch (error) {
      console.error('Error polling payment status:', error);
      throw error;
    }
  }
  
  throw new Error('Payment polling timeout');
}

/**
 * Determine payment status for app
 */
export function getAppPaymentStatus(midtransStatus: string): 'pending' | 'success' | 'failed' | 'cancelled' {
  switch (midtransStatus) {
    case 'settlement':
    case 'capture':
      return 'success';
    case 'pending':
      return 'pending';
    case 'deny':
    case 'expire':
    case 'cancel':
    case 'chargeback':
      return 'failed';
    default:
      return 'pending';
  }
}

/**
 * Format payment status for display
 */
export function formatPaymentStatus(status: string): string {
  const statusMap: Record<string, string> = {
    'pending': 'Pending Payment',
    'settlement': 'Payment Successful',
    'capture': 'Payment Captured',
    'deny': 'Payment Declined',
    'expire': 'Payment Expired',
    'cancel': 'Payment Cancelled',
    'refund': 'Refunded',
    'chargeback': 'Chargeback',
  };
  
  return statusMap[status] || status;
}

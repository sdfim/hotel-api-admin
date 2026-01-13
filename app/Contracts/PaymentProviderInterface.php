<?php

namespace App\Contracts;

interface PaymentProviderInterface
{
    /**
     * @return mixed
     */
    public function createPaymentIntent(array $data);

    /**
     * @return mixed
     */
    public function retrievePaymentConsent($id);

    /**
     * @return mixed
     */
    public function confirmationPaymentIntent(array $data);

    /**
     * @return mixed
     */
    public function retrievePaymentIntent($id);

    public function getTransactionByBookingId($bookingId): mixed;

    /**
     * @param string $bookingId
     * @param float $amount
     * @return mixed
     */
    public function createPaymentIntentMoFoF(string $bookingId, float $amount);
}

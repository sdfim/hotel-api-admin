<?php
namespace App\Contracts;

interface PaymentProviderInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function createPaymentIntent(array $data);

    /**
     * @param $id
     * @return mixed
     */
    public function retrievePaymentConsent($id);

    /**
     * @param array $data
     * @return mixed
     */
    public function confirmationPaymentIntent(array $data);

    /**
     * @param $id
     * @return mixed
     */
    public function retrievePaymentIntent($id);

    /**
     * @param $bookingId
     * @return mixed
     */
    public function getTransactionByBookingId($bookingId);
}

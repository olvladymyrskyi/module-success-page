<?php

namespace Yaga\Success\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


class Success extends Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderInterface
     */
    protected $order;

    private $deliveryLess = 5;



    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->getOrder();
        parent::__construct($context, $data);

    }



    /**
     * @return OrderInterface
     */
    protected function getOrder()
    {
        if (!$this->order) {
            $this->order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());
        }

        return $this->order;
    }

    /**
     * @param $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }


    /**
     * @return string
     */
    public function getEmail(){
        return $this->order->getCustomerEmail();
    }

    /**
     * @return string
     */
    public function getOrderNumber(){
        return $this->order->getIncrementId();
    }

    public function getDeliveryDate(){
        return $this->order->getData('delivery_date');
    }

    public function isDeliveryLess(){

        if(!$this->getDeliveryDate()){
            return false;
        }

        $orderCreated = $this->order->getCreatedAt();
        $calculateStamp =  strtotime('+5 days', strtotime($orderCreated));

        if(($calculateStamp - strtotime($this->getDeliveryDate())) > 0){
            return true;
        }

        return false;
    }


    /**
     * @return mixed
     */
    public function getContent()
    {
       $html = '';

        if($this->isDeliveryLess()){
            $html .= '<!-- BEGIN - Javascript EasyReviews Addon -->';
            $html .= '<script src="https://feedback.shopvote.de/srt-v4.min.js"></script>';
            $html .= '<script type="text/javascript">';
            $html .= 'var myToken = "6d63a4db93c7c4cc549c41458dbbeb30";';
            $html .= "var mySrc = ('https:' === document.location.protocol ? 'https' : 'http');";
            $html .= "var myLanguage = 'DE';";
            $html .= 'loadSRT(myToken, mySrc);';
            $html .= '</script>';
            $html .= '<!-- END - Javascript EasyReviews Addon -->';

            $html .= '<!-- BEGIN - EasyReviews Addon | www.shopvote.de -->';
            $html .= '<div id="srt-customer-data" style="display:none;">';
            $html .= '<span id="srt-customer-email">' . $this->getEmail() . '</span>';
            $html .= '<span id="srt-customer-reference">' .  $this->getOrderNumber() . '</span>';
            $html .= '</div>';
            $html .= '<!-- END - EasyReviews Addon | www.shopvote.de -->';
        }

        return $html;
    }
}

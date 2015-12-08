<?php

/**
 * @desc 新增Case时候的两个组状态值
 * @author YangLong
 * @date 2015-04-22
 */
class DisputeExplanationCodeType2
{

    /**
     * @desc 未付款时的可能值
     * @var array
     */
    public static $BuyerHasNotPaid = array(
        'BuyerHasNotResponded',
        'BuyerNotClearedToPay',
        'BuyerNotPaid',
        'BuyerPaymentNotReceivedOrCleared',
        'BuyerRefusedToPay',
        'OtherExplanation',
        'SellerDoesntShipToCountry',
        'ShippingAddressNotConfirmed'
    );

    /**
     * @desc 取消订单时的可能值
     * @var array
     */
    public static $TransactionMutuallyCanceled = array(
        'BuyerNoLongerWantsItem',
        'BuyerPurchasingMistake',
        'BuyerReturnedItemForRefund',
        'OtherExplanation',
        'SellerDoesntShipToCountry',
        'SellerRanOutOfStock',
        'ShippingAddressNotConfirmed',
        'UnableToResolveTerms'
    );

    /**
     * @desc 类型值
     * @var array
     */
    public static $type = array(
        'BuyerHasNotPaid',
        'TransactionMutuallyCanceled'
    );
}
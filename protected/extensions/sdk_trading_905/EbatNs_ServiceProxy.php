<?php
// $Id: ClientProxy.tpl.php,v 1.6 2008-06-05 08:58:31 michael Exp $
// $Log: ClientProxy.tpl.php,v $
// Revision 1.6  2008-06-05 08:58:31  michael
// switched to getAck()
//
// Revision 1.5  2008/05/28 16:53:18  michael
// fixed and moved method getErrorsToString() to Client
//
// Revision 1.4  2008/05/05 15:51:31  carsten
// *** empty log message ***
//
// Revision 1.3  2008/05/04 15:24:03  carsten
// *** empty log message ***
//
// Revision 1.2  2008/05/02 15:04:05  carsten
// Initial, PHP5
//
// 
// auto-generated 2/3/15 3:23 AM 
// Ebay-Schema Version 905
//
/**
 * Load files we depend on.
 */

require_once 'EbatNs_Client.php';
require_once 'EbatNs_Session.php';

/**
 * The WSDL version the SDK is built against.
 */
define('EBAY_WSDL_VERSION', '905');

/**
 * This class is the basic interface to the eBay-Webserice for the user.
 * We generated the "proxy" externally as the SOAP-wsdl proxy generator does
 * not really did what we needed.
 */
class EbatNs_ServiceProxy extends EbatNs_Client
{
    /**
     * Setup the ServiceProxy 
     *
     * @param mixed $sessionOrConfig Could be either a path to a config-file or a EbatNs_Session-object
     * @param string $converter Name of the converter class used, defaults to 'EbatNs_DataConverterIso' for convertion from uft8 to iso-8859-1
     */
    function __construct($sessionOrConfig, $converter = 'EbatNs_DataConverterIso')
    {
        if ($sessionOrConfig instanceof EbatNs_Session)
        {
			// Initialize the SOAP Client.
			parent::__construct($sessionOrConfig, $converter);
		}
		else
		{
			// assume that $session is the path to the config-file
			//
		    if (is_string($sessionOrConfig))
			{
				$session = new EbatNs_Session($sessionOrConfig);
				parent::__construct($session, $converter);
			}
		}
    }

    /**
     * Checks if the response has errors (from the eBay API side)
     *
     * @param AbstractResponseType $response	A response returned by any of the eBay API calls
     * @param Boolean $ignoreWarnings	true (default) will ignore warnings, so we detect ONLY real failures ...
     * @return Boolean
     */
	function isGood($response, $ignoreWarnings = true)
	{
		if ($ignoreWarnings)
			return ($response->getAck() == 'Success' || $response->getAck() == 'Warning');		
		else
			return ($response->getAck() == 'Success');
	}

	/**
	 * Checks if the response had failures
	 *
	 * @param AbstractResponseType $response	A response returned by any of the eBay API calls
	 * @return Boolean
	 */
	function isFailure($response)
	{
		return ($response->getAck() == 'Failure');
	}
	
	/**
	 * @return AddDisputeResponse
	 * @param AddDisputeRequest
	 **/
	function AddDispute($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddDispute', $request);
	}

	/**
	 * @return AddDisputeResponseResponse
	 * @param AddDisputeResponseRequest
	 **/
	function AddDisputeResponse($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddDisputeResponse', $request);
	}

	/**
	 * @return AddFixedPriceItemResponse
	 * @param AddFixedPriceItemRequest
	 **/
	function AddFixedPriceItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddFixedPriceItem', $request);
	}

	/**
	 * @return AddItemResponse
	 * @param AddItemRequest
	 **/
	function AddItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddItem', $request);
	}

	/**
	 * @return AddItemFromSellingManagerTemplateResponse
	 * @param AddItemFromSellingManagerTemplateRequest
	 **/
	function AddItemFromSellingManagerTemplate($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddItemFromSellingManagerTemplate', $request);
	}

	/**
	 * @return AddItemsResponse
	 * @param AddItemsRequest
	 **/
	function AddItems($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddItems', $request);
	}

	/**
	 * @return AddMemberMessageAAQToPartnerResponse
	 * @param AddMemberMessageAAQToPartnerRequest
	 **/
	function AddMemberMessageAAQToPartner($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddMemberMessageAAQToPartner', $request);
	}

	/**
	 * @return AddMemberMessageRTQResponse
	 * @param AddMemberMessageRTQRequest
	 **/
	function AddMemberMessageRTQ($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddMemberMessageRTQ', $request);
	}

	/**
	 * @return AddMemberMessagesAAQToBidderResponse
	 * @param AddMemberMessagesAAQToBidderRequest
	 **/
	function AddMemberMessagesAAQToBidder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddMemberMessagesAAQToBidder', $request);
	}

	/**
	 * @return AddOrderResponse
	 * @param AddOrderRequest
	 **/
	function AddOrder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddOrder', $request);
	}

	/**
	 * @return AddSecondChanceItemResponse
	 * @param AddSecondChanceItemRequest
	 **/
	function AddSecondChanceItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddSecondChanceItem', $request);
	}

	/**
	 * @return AddSellingManagerInventoryFolderResponse
	 * @param AddSellingManagerInventoryFolderRequest
	 **/
	function AddSellingManagerInventoryFolder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddSellingManagerInventoryFolder', $request);
	}

	/**
	 * @return AddSellingManagerProductResponse
	 * @param AddSellingManagerProductRequest
	 **/
	function AddSellingManagerProduct($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddSellingManagerProduct', $request);
	}

	/**
	 * @return AddSellingManagerTemplateResponse
	 * @param AddSellingManagerTemplateRequest
	 **/
	function AddSellingManagerTemplate($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddSellingManagerTemplate', $request);
	}

	/**
	 * @return AddToItemDescriptionResponse
	 * @param AddToItemDescriptionRequest
	 **/
	function AddToItemDescription($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddToItemDescription', $request);
	}

	/**
	 * @return AddToWatchListResponse
	 * @param AddToWatchListRequest
	 **/
	function AddToWatchList($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddToWatchList', $request);
	}

	/**
	 * @return AddTransactionConfirmationItemResponse
	 * @param AddTransactionConfirmationItemRequest
	 **/
	function AddTransactionConfirmationItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('AddTransactionConfirmationItem', $request);
	}

	/**
	 * @return CompleteSaleResponse
	 * @param CompleteSaleRequest
	 **/
	function CompleteSale($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('CompleteSale', $request);
	}

	/**
	 * @return ConfirmIdentityResponse
	 * @param ConfirmIdentityRequest
	 **/
	function ConfirmIdentity($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ConfirmIdentity', $request);
	}

	/**
	 * @return DeleteMyMessagesResponse
	 * @param DeleteMyMessagesRequest
	 **/
	function DeleteMyMessages($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('DeleteMyMessages', $request);
	}

	/**
	 * @return DeleteSellingManagerInventoryFolderResponse
	 * @param DeleteSellingManagerInventoryFolderRequest
	 **/
	function DeleteSellingManagerInventoryFolder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('DeleteSellingManagerInventoryFolder', $request);
	}

	/**
	 * @return DeleteSellingManagerItemAutomationRuleResponse
	 * @param DeleteSellingManagerItemAutomationRuleRequest
	 **/
	function DeleteSellingManagerItemAutomationRule($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('DeleteSellingManagerItemAutomationRule', $request);
	}

	/**
	 * @return DeleteSellingManagerProductResponse
	 * @param DeleteSellingManagerProductRequest
	 **/
	function DeleteSellingManagerProduct($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('DeleteSellingManagerProduct', $request);
	}

	/**
	 * @return DeleteSellingManagerTemplateResponse
	 * @param DeleteSellingManagerTemplateRequest
	 **/
	function DeleteSellingManagerTemplate($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('DeleteSellingManagerTemplate', $request);
	}

	/**
	 * @return DeleteSellingManagerTemplateAutomationRuleResponse
	 * @param DeleteSellingManagerTemplateAutomationRuleRequest
	 **/
	function DeleteSellingManagerTemplateAutomationRule($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('DeleteSellingManagerTemplateAutomationRule', $request);
	}

	/**
	 * @return DisableUnpaidItemAssistanceResponse
	 * @param DisableUnpaidItemAssistanceRequest
	 **/
	function DisableUnpaidItemAssistance($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('DisableUnpaidItemAssistance', $request);
	}

	/**
	 * @return EndFixedPriceItemResponse
	 * @param EndFixedPriceItemRequest
	 **/
	function EndFixedPriceItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('EndFixedPriceItem', $request);
	}

	/**
	 * @return EndItemResponse
	 * @param EndItemRequest
	 **/
	function EndItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('EndItem', $request);
	}

	/**
	 * @return EndItemsResponse
	 * @param EndItemsRequest
	 **/
	function EndItems($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('EndItems', $request);
	}

	/**
	 * @return ExtendSiteHostedPicturesResponse
	 * @param ExtendSiteHostedPicturesRequest
	 **/
	function ExtendSiteHostedPictures($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ExtendSiteHostedPictures', $request);
	}

	/**
	 * @return FetchTokenResponse
	 * @param FetchTokenRequest
	 **/
	function FetchToken($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('FetchToken', $request);
	}

	/**
	 * @return GetAccountResponse
	 * @param GetAccountRequest
	 **/
	function GetAccount($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetAccount', $request);
	}

	/**
	 * @return GetAdFormatLeadsResponse
	 * @param GetAdFormatLeadsRequest
	 **/
	function GetAdFormatLeads($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetAdFormatLeads', $request);
	}

	/**
	 * @return GetAllBiddersResponse
	 * @param GetAllBiddersRequest
	 **/
	function GetAllBidders($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetAllBidders', $request);
	}

	/**
	 * @return GetApiAccessRulesResponse
	 * @param GetApiAccessRulesRequest
	 **/
	function GetApiAccessRules($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetApiAccessRules', $request);
	}

	/**
	 * @return GetAttributesCSResponse
	 * @param GetAttributesCSRequest
	 **/
	function GetAttributesCS($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetAttributesCS', $request);
	}

	/**
	 * @return GetAttributesXSLResponse
	 * @param GetAttributesXSLRequest
	 **/
	function GetAttributesXSL($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetAttributesXSL', $request);
	}

	/**
	 * @return GetBestOffersResponse
	 * @param GetBestOffersRequest
	 **/
	function GetBestOffers($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetBestOffers', $request);
	}

	/**
	 * @return GetBidderListResponse
	 * @param GetBidderListRequest
	 **/
	function GetBidderList($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetBidderList', $request);
	}

	/**
	 * @return GetCategoriesResponse
	 * @param GetCategoriesRequest
	 **/
	function GetCategories($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetCategories', $request);
	}

	/**
	 * @return GetCategory2CSResponse
	 * @param GetCategory2CSRequest
	 **/
	function GetCategory2CS($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetCategory2CS', $request);
	}

	/**
	 * @return GetCategoryFeaturesResponse
	 * @param GetCategoryFeaturesRequest
	 **/
	function GetCategoryFeatures($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetCategoryFeatures', $request);
	}

	/**
	 * @return GetCategoryMappingsResponse
	 * @param GetCategoryMappingsRequest
	 **/
	function GetCategoryMappings($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetCategoryMappings', $request);
	}

	/**
	 * @return GetCategorySpecificsResponse
	 * @param GetCategorySpecificsRequest
	 **/
	function GetCategorySpecifics($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetCategorySpecifics', $request);
	}

	/**
	 * @return GetChallengeTokenResponse
	 * @param GetChallengeTokenRequest
	 **/
	function GetChallengeToken($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetChallengeToken', $request);
	}

	/**
	 * @return GetCharitiesResponse
	 * @param GetCharitiesRequest
	 **/
	function GetCharities($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetCharities', $request);
	}

	/**
	 * @return GetClientAlertsAuthTokenResponse
	 * @param GetClientAlertsAuthTokenRequest
	 **/
	function GetClientAlertsAuthToken($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetClientAlertsAuthToken', $request);
	}

	/**
	 * @return GetContextualKeywordsResponse
	 * @param GetContextualKeywordsRequest
	 **/
	function GetContextualKeywords($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetContextualKeywords', $request);
	}

	/**
	 * @return GetCrossPromotionsResponse
	 * @param GetCrossPromotionsRequest
	 **/
	function GetCrossPromotions($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetCrossPromotions', $request);
	}

	/**
	 * @return GetDescriptionTemplatesResponse
	 * @param GetDescriptionTemplatesRequest
	 **/
	function GetDescriptionTemplates($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetDescriptionTemplates', $request);
	}

	/**
	 * @return GetDisputeResponse
	 * @param GetDisputeRequest
	 **/
	function GetDispute($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetDispute', $request);
	}

	/**
	 * @return GetFeedbackResponse
	 * @param GetFeedbackRequest
	 **/
	function GetFeedback($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetFeedback', $request);
	}

	/**
	 * @return GetHighBiddersResponse
	 * @param GetHighBiddersRequest
	 **/
	function GetHighBidders($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetHighBidders', $request);
	}

	/**
	 * @return GetItemResponse
	 * @param GetItemRequest
	 **/
	function GetItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetItem', $request);
	}

	/**
	 * @return GetItemRecommendationsResponse
	 * @param GetItemRecommendationsRequest
	 **/
	function GetItemRecommendations($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetItemRecommendations', $request);
	}

	/**
	 * @return GetItemShippingResponse
	 * @param GetItemShippingRequest
	 **/
	function GetItemShipping($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetItemShipping', $request);
	}

	/**
	 * @return GetItemTransactionsResponse
	 * @param GetItemTransactionsRequest
	 **/
	function GetItemTransactions($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetItemTransactions', $request);
	}

	/**
	 * @return GetItemsAwaitingFeedbackResponse
	 * @param GetItemsAwaitingFeedbackRequest
	 **/
	function GetItemsAwaitingFeedback($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetItemsAwaitingFeedback', $request);
	}

	/**
	 * @return GetMemberMessagesResponse
	 * @param GetMemberMessagesRequest
	 **/
	function GetMemberMessages($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetMemberMessages', $request);
	}

	/**
	 * @return GetMessagePreferencesResponse
	 * @param GetMessagePreferencesRequest
	 **/
	function GetMessagePreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetMessagePreferences', $request);
	}

	/**
	 * @return GetMyMessagesResponse
	 * @param GetMyMessagesRequest
	 **/
	function GetMyMessages($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetMyMessages', $request);
	}

	/**
	 * @return GetMyeBayBuyingResponse
	 * @param GetMyeBayBuyingRequest
	 **/
	function GetMyeBayBuying($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetMyeBayBuying', $request);
	}

	/**
	 * @return GetMyeBayRemindersResponse
	 * @param GetMyeBayRemindersRequest
	 **/
	function GetMyeBayReminders($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetMyeBayReminders', $request);
	}

	/**
	 * @return GetMyeBaySellingResponse
	 * @param GetMyeBaySellingRequest
	 **/
	function GetMyeBaySelling($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetMyeBaySelling', $request);
	}

	/**
	 * @return GetNotificationPreferencesResponse
	 * @param GetNotificationPreferencesRequest
	 **/
	function GetNotificationPreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetNotificationPreferences', $request);
	}

	/**
	 * @return GetNotificationsUsageResponse
	 * @param GetNotificationsUsageRequest
	 **/
	function GetNotificationsUsage($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetNotificationsUsage', $request);
	}

	/**
	 * @return GetOrderTransactionsResponse
	 * @param GetOrderTransactionsRequest
	 **/
	function GetOrderTransactions($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetOrderTransactions', $request);
	}

	/**
	 * @return GetOrdersResponse
	 * @param GetOrdersRequest
	 **/
	function GetOrders($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetOrders', $request);
	}

	/**
	 * @return GetPictureManagerDetailsResponse
	 * @param GetPictureManagerDetailsRequest
	 **/
	function GetPictureManagerDetails($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetPictureManagerDetails', $request);
	}

	/**
	 * @return GetPictureManagerOptionsResponse
	 * @param GetPictureManagerOptionsRequest
	 **/
	function GetPictureManagerOptions($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetPictureManagerOptions', $request);
	}

	/**
	 * @return GetProductFamilyMembersResponse
	 * @param GetProductFamilyMembersRequest
	 **/
	function GetProductFamilyMembers($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetProductFamilyMembers', $request);
	}

	/**
	 * @return GetProductFinderResponse
	 * @param GetProductFinderRequest
	 **/
	function GetProductFinder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetProductFinder', $request);
	}

	/**
	 * @return GetProductFinderXSLResponse
	 * @param GetProductFinderXSLRequest
	 **/
	function GetProductFinderXSL($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetProductFinderXSL', $request);
	}

	/**
	 * @return GetProductSearchPageResponse
	 * @param GetProductSearchPageRequest
	 **/
	function GetProductSearchPage($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetProductSearchPage', $request);
	}

	/**
	 * @return GetProductSearchResultsResponse
	 * @param GetProductSearchResultsRequest
	 **/
	function GetProductSearchResults($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetProductSearchResults', $request);
	}

	/**
	 * @return GetProductSellingPagesResponse
	 * @param GetProductSellingPagesRequest
	 **/
	function GetProductSellingPages($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetProductSellingPages', $request);
	}

	/**
	 * @return GetPromotionRulesResponse
	 * @param GetPromotionRulesRequest
	 **/
	function GetPromotionRules($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetPromotionRules', $request);
	}

	/**
	 * @return GetPromotionalSaleDetailsResponse
	 * @param GetPromotionalSaleDetailsRequest
	 **/
	function GetPromotionalSaleDetails($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetPromotionalSaleDetails', $request);
	}

	/**
	 * @return GetSellerDashboardResponse
	 * @param GetSellerDashboardRequest
	 **/
	function GetSellerDashboard($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellerDashboard', $request);
	}

	/**
	 * @return GetSellerEventsResponse
	 * @param GetSellerEventsRequest
	 **/
	function GetSellerEvents($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellerEvents', $request);
	}

	/**
	 * @return GetSellerListResponse
	 * @param GetSellerListRequest
	 **/
	function GetSellerList($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellerList', $request);
	}

	/**
	 * @return GetSellerPaymentsResponse
	 * @param GetSellerPaymentsRequest
	 **/
	function GetSellerPayments($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellerPayments', $request);
	}

	/**
	 * @return GetSellerTransactionsResponse
	 * @param GetSellerTransactionsRequest
	 **/
	function GetSellerTransactions($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellerTransactions', $request);
	}

	/**
	 * @return GetSellingManagerAlertsResponse
	 * @param GetSellingManagerAlertsRequest
	 **/
	function GetSellingManagerAlerts($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerAlerts', $request);
	}

	/**
	 * @return GetSellingManagerEmailLogResponse
	 * @param GetSellingManagerEmailLogRequest
	 **/
	function GetSellingManagerEmailLog($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerEmailLog', $request);
	}

	/**
	 * @return GetSellingManagerInventoryResponse
	 * @param GetSellingManagerInventoryRequest
	 **/
	function GetSellingManagerInventory($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerInventory', $request);
	}

	/**
	 * @return GetSellingManagerInventoryFolderResponse
	 * @param GetSellingManagerInventoryFolderRequest
	 **/
	function GetSellingManagerInventoryFolder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerInventoryFolder', $request);
	}

	/**
	 * @return GetSellingManagerItemAutomationRuleResponse
	 * @param GetSellingManagerItemAutomationRuleRequest
	 **/
	function GetSellingManagerItemAutomationRule($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerItemAutomationRule', $request);
	}

	/**
	 * @return GetSellingManagerSaleRecordResponse
	 * @param GetSellingManagerSaleRecordRequest
	 **/
	function GetSellingManagerSaleRecord($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerSaleRecord', $request);
	}

	/**
	 * @return GetSellingManagerSoldListingsResponse
	 * @param GetSellingManagerSoldListingsRequest
	 **/
	function GetSellingManagerSoldListings($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerSoldListings', $request);
	}

	/**
	 * @return GetSellingManagerTemplateAutomationRuleResponse
	 * @param GetSellingManagerTemplateAutomationRuleRequest
	 **/
	function GetSellingManagerTemplateAutomationRule($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerTemplateAutomationRule', $request);
	}

	/**
	 * @return GetSellingManagerTemplatesResponse
	 * @param GetSellingManagerTemplatesRequest
	 **/
	function GetSellingManagerTemplates($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSellingManagerTemplates', $request);
	}

	/**
	 * @return GetSessionIDResponse
	 * @param GetSessionIDRequest
	 **/
	function GetSessionID($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSessionID', $request);
	}

	/**
	 * @return GetShippingDiscountProfilesResponse
	 * @param GetShippingDiscountProfilesRequest
	 **/
	function GetShippingDiscountProfiles($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetShippingDiscountProfiles', $request);
	}

	/**
	 * @return GetStoreResponse
	 * @param GetStoreRequest
	 **/
	function GetStore($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetStore', $request);
	}

	/**
	 * @return GetStoreCategoryUpdateStatusResponse
	 * @param GetStoreCategoryUpdateStatusRequest
	 **/
	function GetStoreCategoryUpdateStatus($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetStoreCategoryUpdateStatus', $request);
	}

	/**
	 * @return GetStoreCustomPageResponse
	 * @param GetStoreCustomPageRequest
	 **/
	function GetStoreCustomPage($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetStoreCustomPage', $request);
	}

	/**
	 * @return GetStoreOptionsResponse
	 * @param GetStoreOptionsRequest
	 **/
	function GetStoreOptions($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetStoreOptions', $request);
	}

	/**
	 * @return GetStorePreferencesResponse
	 * @param GetStorePreferencesRequest
	 **/
	function GetStorePreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetStorePreferences', $request);
	}

	/**
	 * @return GetSuggestedCategoriesResponse
	 * @param GetSuggestedCategoriesRequest
	 **/
	function GetSuggestedCategories($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetSuggestedCategories', $request);
	}

	/**
	 * @return GetTaxTableResponse
	 * @param GetTaxTableRequest
	 **/
	function GetTaxTable($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetTaxTable', $request);
	}

	/**
	 * @return GetTokenStatusResponse
	 * @param GetTokenStatusRequest
	 **/
	function GetTokenStatus($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetTokenStatus', $request);
	}

	/**
	 * @return GetUserResponse
	 * @param GetUserRequest
	 **/
	function GetUser($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetUser', $request);
	}

	/**
	 * @return GetUserContactDetailsResponse
	 * @param GetUserContactDetailsRequest
	 **/
	function GetUserContactDetails($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetUserContactDetails', $request);
	}

	/**
	 * @return GetUserDisputesResponse
	 * @param GetUserDisputesRequest
	 **/
	function GetUserDisputes($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetUserDisputes', $request);
	}

	/**
	 * @return GetUserPreferencesResponse
	 * @param GetUserPreferencesRequest
	 **/
	function GetUserPreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetUserPreferences', $request);
	}

	/**
	 * @return GetVeROReasonCodeDetailsResponse
	 * @param GetVeROReasonCodeDetailsRequest
	 **/
	function GetVeROReasonCodeDetails($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetVeROReasonCodeDetails', $request);
	}

	/**
	 * @return GetVeROReportStatusResponse
	 * @param GetVeROReportStatusRequest
	 **/
	function GetVeROReportStatus($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetVeROReportStatus', $request);
	}

	/**
	 * @return GetWantItNowPostResponse
	 * @param GetWantItNowPostRequest
	 **/
	function GetWantItNowPost($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetWantItNowPost', $request);
	}

	/**
	 * @return GetWantItNowSearchResultsResponse
	 * @param GetWantItNowSearchResultsRequest
	 **/
	function GetWantItNowSearchResults($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GetWantItNowSearchResults', $request);
	}

	/**
	 * @return GeteBayDetailsResponse
	 * @param GeteBayDetailsRequest
	 **/
	function GeteBayDetails($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GeteBayDetails', $request);
	}

	/**
	 * @return GeteBayOfficialTimeResponse
	 * @param GeteBayOfficialTimeRequest
	 **/
	function GeteBayOfficialTime($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('GeteBayOfficialTime', $request);
	}

	/**
	 * @return IssueRefundResponse
	 * @param IssueRefundRequest
	 **/
	function IssueRefund($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('IssueRefund', $request);
	}

	/**
	 * @return LeaveFeedbackResponse
	 * @param LeaveFeedbackRequest
	 **/
	function LeaveFeedback($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('LeaveFeedback', $request);
	}

	/**
	 * @return MoveSellingManagerInventoryFolderResponse
	 * @param MoveSellingManagerInventoryFolderRequest
	 **/
	function MoveSellingManagerInventoryFolder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('MoveSellingManagerInventoryFolder', $request);
	}

	/**
	 * @return PlaceOfferResponse
	 * @param PlaceOfferRequest
	 **/
	function PlaceOffer($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('PlaceOffer', $request);
	}

	/**
	 * @return RelistFixedPriceItemResponse
	 * @param RelistFixedPriceItemRequest
	 **/
	function RelistFixedPriceItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('RelistFixedPriceItem', $request);
	}

	/**
	 * @return RelistItemResponse
	 * @param RelistItemRequest
	 **/
	function RelistItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('RelistItem', $request);
	}

	/**
	 * @return RemoveFromWatchListResponse
	 * @param RemoveFromWatchListRequest
	 **/
	function RemoveFromWatchList($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('RemoveFromWatchList', $request);
	}

	/**
	 * @return RespondToBestOfferResponse
	 * @param RespondToBestOfferRequest
	 **/
	function RespondToBestOffer($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('RespondToBestOffer', $request);
	}

	/**
	 * @return RespondToFeedbackResponse
	 * @param RespondToFeedbackRequest
	 **/
	function RespondToFeedback($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('RespondToFeedback', $request);
	}

	/**
	 * @return RespondToWantItNowPostResponse
	 * @param RespondToWantItNowPostRequest
	 **/
	function RespondToWantItNowPost($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('RespondToWantItNowPost', $request);
	}

	/**
	 * @return ReviseCheckoutStatusResponse
	 * @param ReviseCheckoutStatusRequest
	 **/
	function ReviseCheckoutStatus($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseCheckoutStatus', $request);
	}

	/**
	 * @return ReviseFixedPriceItemResponse
	 * @param ReviseFixedPriceItemRequest
	 **/
	function ReviseFixedPriceItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseFixedPriceItem', $request);
	}

	/**
	 * @return ReviseInventoryStatusResponse
	 * @param ReviseInventoryStatusRequest
	 **/
	function ReviseInventoryStatus($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseInventoryStatus', $request);
	}

	/**
	 * @return ReviseItemResponse
	 * @param ReviseItemRequest
	 **/
	function ReviseItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseItem', $request);
	}

	/**
	 * @return ReviseMyMessagesResponse
	 * @param ReviseMyMessagesRequest
	 **/
	function ReviseMyMessages($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseMyMessages', $request);
	}

	/**
	 * @return ReviseMyMessagesFoldersResponse
	 * @param ReviseMyMessagesFoldersRequest
	 **/
	function ReviseMyMessagesFolders($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseMyMessagesFolders', $request);
	}

	/**
	 * @return ReviseSellingManagerInventoryFolderResponse
	 * @param ReviseSellingManagerInventoryFolderRequest
	 **/
	function ReviseSellingManagerInventoryFolder($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseSellingManagerInventoryFolder', $request);
	}

	/**
	 * @return ReviseSellingManagerProductResponse
	 * @param ReviseSellingManagerProductRequest
	 **/
	function ReviseSellingManagerProduct($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseSellingManagerProduct', $request);
	}

	/**
	 * @return ReviseSellingManagerSaleRecordResponse
	 * @param ReviseSellingManagerSaleRecordRequest
	 **/
	function ReviseSellingManagerSaleRecord($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseSellingManagerSaleRecord', $request);
	}

	/**
	 * @return ReviseSellingManagerTemplateResponse
	 * @param ReviseSellingManagerTemplateRequest
	 **/
	function ReviseSellingManagerTemplate($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ReviseSellingManagerTemplate', $request);
	}

	/**
	 * @return RevokeTokenResponse
	 * @param RevokeTokenRequest
	 **/
	function RevokeToken($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('RevokeToken', $request);
	}

	/**
	 * @return SaveItemToSellingManagerTemplateResponse
	 * @param SaveItemToSellingManagerTemplateRequest
	 **/
	function SaveItemToSellingManagerTemplate($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SaveItemToSellingManagerTemplate', $request);
	}

	/**
	 * @return SellerReverseDisputeResponse
	 * @param SellerReverseDisputeRequest
	 **/
	function SellerReverseDispute($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SellerReverseDispute', $request);
	}

	/**
	 * @return SendInvoiceResponse
	 * @param SendInvoiceRequest
	 **/
	function SendInvoice($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SendInvoice', $request);
	}

	/**
	 * @return SetMessagePreferencesResponse
	 * @param SetMessagePreferencesRequest
	 **/
	function SetMessagePreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetMessagePreferences', $request);
	}

	/**
	 * @return SetNotificationPreferencesResponse
	 * @param SetNotificationPreferencesRequest
	 **/
	function SetNotificationPreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetNotificationPreferences', $request);
	}

	/**
	 * @return SetPictureManagerDetailsResponse
	 * @param SetPictureManagerDetailsRequest
	 **/
	function SetPictureManagerDetails($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetPictureManagerDetails', $request);
	}

	/**
	 * @return SetPromotionalSaleResponse
	 * @param SetPromotionalSaleRequest
	 **/
	function SetPromotionalSale($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetPromotionalSale', $request);
	}

	/**
	 * @return SetPromotionalSaleListingsResponse
	 * @param SetPromotionalSaleListingsRequest
	 **/
	function SetPromotionalSaleListings($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetPromotionalSaleListings', $request);
	}

	/**
	 * @return SetSellingManagerFeedbackOptionsResponse
	 * @param SetSellingManagerFeedbackOptionsRequest
	 **/
	function SetSellingManagerFeedbackOptions($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetSellingManagerFeedbackOptions', $request);
	}

	/**
	 * @return SetSellingManagerItemAutomationRuleResponse
	 * @param SetSellingManagerItemAutomationRuleRequest
	 **/
	function SetSellingManagerItemAutomationRule($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetSellingManagerItemAutomationRule', $request);
	}

	/**
	 * @return SetSellingManagerTemplateAutomationRuleResponse
	 * @param SetSellingManagerTemplateAutomationRuleRequest
	 **/
	function SetSellingManagerTemplateAutomationRule($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetSellingManagerTemplateAutomationRule', $request);
	}

	/**
	 * @return SetShippingDiscountProfilesResponse
	 * @param SetShippingDiscountProfilesRequest
	 **/
	function SetShippingDiscountProfiles($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetShippingDiscountProfiles', $request);
	}

	/**
	 * @return SetStoreResponse
	 * @param SetStoreRequest
	 **/
	function SetStore($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetStore', $request);
	}

	/**
	 * @return SetStoreCategoriesResponse
	 * @param SetStoreCategoriesRequest
	 **/
	function SetStoreCategories($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetStoreCategories', $request);
	}

	/**
	 * @return SetStoreCustomPageResponse
	 * @param SetStoreCustomPageRequest
	 **/
	function SetStoreCustomPage($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetStoreCustomPage', $request);
	}

	/**
	 * @return SetStorePreferencesResponse
	 * @param SetStorePreferencesRequest
	 **/
	function SetStorePreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetStorePreferences', $request);
	}

	/**
	 * @return SetTaxTableResponse
	 * @param SetTaxTableRequest
	 **/
	function SetTaxTable($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetTaxTable', $request);
	}

	/**
	 * @return SetUserNotesResponse
	 * @param SetUserNotesRequest
	 **/
	function SetUserNotes($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetUserNotes', $request);
	}

	/**
	 * @return SetUserPreferencesResponse
	 * @param SetUserPreferencesRequest
	 **/
	function SetUserPreferences($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('SetUserPreferences', $request);
	}

	/**
	 * @return UploadSiteHostedPicturesResponse
	 * @param UploadSiteHostedPicturesRequest
	 **/
	function UploadSiteHostedPictures($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->callXmlStyle('UploadSiteHostedPictures', $request);
	}

	/**
	 * @return ValidateChallengeInputResponse
	 * @param ValidateChallengeInputRequest
	 **/
	function ValidateChallengeInput($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ValidateChallengeInput', $request);
	}

	/**
	 * @return ValidateTestUserRegistrationResponse
	 * @param ValidateTestUserRegistrationRequest
	 **/
	function ValidateTestUserRegistration($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('ValidateTestUserRegistration', $request);
	}

	/**
	 * @return VeROReportItemsResponse
	 * @param VeROReportItemsRequest
	 **/
	function VeROReportItems($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('VeROReportItems', $request);
	}

	/**
	 * @return VerifyAddFixedPriceItemResponse
	 * @param VerifyAddFixedPriceItemRequest
	 **/
	function VerifyAddFixedPriceItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('VerifyAddFixedPriceItem', $request);
	}

	/**
	 * @return VerifyAddItemResponse
	 * @param VerifyAddItemRequest
	 **/
	function VerifyAddItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('VerifyAddItem', $request);
	}

	/**
	 * @return VerifyAddSecondChanceItemResponse
	 * @param VerifyAddSecondChanceItemRequest
	 **/
	function VerifyAddSecondChanceItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('VerifyAddSecondChanceItem', $request);
	}

	/**
	 * @return VerifyRelistItemResponse
	 * @param VerifyRelistItemRequest
	 **/
	function VerifyRelistItem($request)
	{
		$request->setVersion(EBAY_WSDL_VERSION);
		return $this->call('VerifyRelistItem', $request);
	}


}
?>

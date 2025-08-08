<?php

namespace App\Providers;

use Microsoft\Graph\Generated\Models;
use Microsoft\Graph\Generated\Models\User;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\GraphRequestAdapter;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\Authentication\BaseBearerTokenAuthenticationProvider;

use App\Providers\DeviceCodeTokenProvider;

class GraphHelper {
    private static string $clientId = '';
    private static string $tenantId = '';
    private static string $graphUserScopes = '';
    private static DeviceCodeTokenProvider $tokenProvider;
    private static GraphServiceClient $userClient;


    public static function getDeviceCodeData()
    {
        self::initializeGraphForUserAuth();
        return self::$tokenProvider->requestDeviceCode();
    }


    public static function initializeGraphForUserAuth(): void
    {
        if (!self::$tokenProvider) {
            self::$tokenProvider = new DeviceCodeTokenProvider(
                env('O365_CLIENT_ID'),
                env('O365_TENANT_ID'),
                env('O365_GRAPH_USER_SCOPES')
            );
        }
    }

    public static function getUserToken(): string {
        self::initializeGraphForUserAuth();
        return self::$tokenProvider
            ->getAuthorizationTokenAsync('https://graph.microsoft.com')
            ->wait();
    }

    

    public static function getUser(): User {
        $configuration = new UserItemRequestBuilderGetRequestConfiguration();
        $configuration->queryParameters = new UserItemRequestBuilderGetQueryParameters();
        $configuration->queryParameters->select = ['displayName', 'mail', 'userPrincipalName'];
        
        return self::$userClient->me()->get($configuration)->wait();
    }

    public static function getInbox(): Models\MessageCollectionResponse {
        $configuration = new MessagesRequestBuilderGetRequestConfiguration();
        $configuration->queryParameters = new MessagesRequestBuilderGetQueryParameters();
        $configuration->queryParameters->select = ['from','isRead','receivedDateTime','subject'];
        $configuration->queryParameters->orderby = ['receivedDateTime DESC'];
        $configuration->queryParameters->top = 25;
        return GraphHelper::$userClient->me()
            ->mailFolders()
            ->byMailFolderId('inbox')
            ->messages()
            ->get($configuration)->wait();
    }

    public static function sendMail(string $subject, string $body, string $recipient): void {
        $message = new Models\Message();
        $message->setSubject($subject);
    
        $itemBody = new Models\ItemBody();
        $itemBody->setContent($body);
        $itemBody->setContentType(new Models\BodyType(Models\BodyType::TEXT));
        $message->setBody($itemBody);
    
        $email = new Models\EmailAddress();
        $email->setAddress($recipient);
        $to = new Models\Recipient();
        $to->setEmailAddress($email);
        $message->setToRecipients([$to]);
    
        $sendMailBody = new SendMailPostRequestBody();
        $sendMailBody->setMessage($message);
    
        GraphHelper::$userClient->me()->sendMail()->post($sendMailBody)->wait();
    }
}


<?php

require 'vendor/autoload.php'; // Make sure to include the autoload file

use App\Providers\GraphHelper;

function initializeGraph(): void {
    GraphHelper::initializeGraphForUserAuth();
}

function displayAccessToken(): void {
    try {
        $token = GraphHelper::getUserToken();
        echo 'User token: '.$token.PHP_EOL;
    } catch (Exception $e) {
        echo 'Error getting access token: '.$e->getMessage().PHP_EOL;
    }
}

function greetUser(): void {
    try {
        // Retrieve the user details
        $user = GraphHelper::getUser();
        
        // Display user's name
        echo 'Hello, ' . $user->getDisplayName() . '!' . PHP_EOL;

        // Display email based on account type
        $email = $user->getMail();
        if (empty($email)) {
            $email = $user->getUserPrincipalName();
        }
        echo 'Email: ' . $email . PHP_EOL . PHP_EOL;
    } catch (Exception $e) {
        echo 'Error getting user: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
    }
}

function listInbox(): void {
    try {
        $messages = GraphHelper::getInbox();

        // Output each message's details
        foreach ($messages->getValue() as $message) {
            print('Message: '.$message->getSubject().PHP_EOL);
            print('  From: '.$message->getFrom()->getEmailAddress()->getName().PHP_EOL);
            $status = $message->getIsRead() ? "Read" : "Unread";
            print('  Status: '.$status.PHP_EOL);
            print('  Received: '.$message->getReceivedDateTime()->format(\DateTimeInterface::RFC2822).PHP_EOL);

        }

        $nextLink = $messages->getOdataNextLink();
        $moreAvailable = isset($nextLink) && $nextLink != '' ? 'True' : 'False';
        print(PHP_EOL.'More messages available? '.$moreAvailable.PHP_EOL.PHP_EOL);
    } catch (Exception $e) {
        print('Error getting user\'s inbox: '.$e->getMessage().PHP_EOL.PHP_EOL);
    }
}

function sendMail(): void {
    try {
        // Send mail to the signed-in user
        // Get the user for their email address
        $user = GraphHelper::getUser();

        // For Work/school accounts, email is in Mail property
        // Personal accounts, email is in UserPrincipalName
        $email = $user->getMail();
        if (empty($email)) {
            $email = $user->getUserPrincipalName();
        }

        GraphHelper::sendMail('Testing Microsoft Graph', 'Hello world!', $email);

        print(PHP_EOL.'Mail sent.'.PHP_EOL.PHP_EOL);
    } catch (Exception $e) {
        print('Error sending mail: '.$e->getMessage().PHP_EOL.PHP_EOL);
    }
}

echo "PHP Graph Tutorial\n";
echo "Please choose one of the following options:\n";
echo "0. Exit\n";
echo "1. Display access token\n";
echo "2. Greet user\n";
echo "3. List my inbox\n";
echo "4. Send mail\n";
echo "5. Make a Graph call\n";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$option = trim($line);

switch ($option) {
    case '0':
        exit;
    case '1':
        initializeGraph();
        displayAccessToken();
        break;
    case '2':
        initializeGraph();
        greetUser();
        break;
    case '3':
        initializeGraph();
        listInbox();
        break;
    case '4':
        initializeGraph();
        sendMail();
        break;
    // Additional cases for other options
    default:
        echo "Invalid option selected.\n";
}


fclose($handle);

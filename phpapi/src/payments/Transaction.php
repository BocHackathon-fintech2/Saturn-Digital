<?php

namespace BankOfCyprus\Payments;

use BankOfCyprus\Accounts\Account;

class Transaction
{
    public $debtor;
    public $creditor;
    public $transactionAmount;
    public $endToEndId;
    public $paymentDetails;
    public $terminalId;
    public $branch;
    public $executionDate;
    public $valueDate;

    /**
     * Transaction constructor.
     */
    public function __construct()
    {
        $this->debtor = new Account();
        $this->creditor = new Account();
        $this->transactionAmount = new TransactionAmount();
    }


}
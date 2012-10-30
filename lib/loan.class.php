<?php  
/** 
 * Loan calculator class
 * 
 * @author Weerapat Poosri <vinboxx@gmail.com>
 */
abstract class Loan 
{
    /** 
     * Is loan was calculated
     * @var boolean
     */
    protected $isCalculated = false;

    /** 
     * Interest rate 
     * @var int
     */
    protected $interest_rate;

    /**
     * Loan amount
     * @var int
     */
    protected $loan_amount;

    /**
     * Loan term in month(s)
     * @var int
     */
    protected $loan_term;

    /**
     * Monthly payments
     * @var int
     */
    protected $monthly_payments;

    /**
     * Payment plan
     * @var array
     */
    protected $payment_plan;

    /**
     * Total payment
     * @var int
     */
    protected $total_payment;

    /**
     * Total interest
     * @var int
     */
    protected $total_interest;

    /**
     * Loan start timestamp
     * @var int
     */
    protected $loan_start_timestamp;

    /** 
     * Sets $interest_rate to a new value
     * 
     * @param int $rate an interest rate
     * @return void
     */
    public function setInterestRate($rate)
    {
        $this->interest_rate = is_numeric($rate) ? $rate : 0;
    }

    /** 
     * Sets $loan_term to a new value
     * 
     * @param int $month a number of months
     * @return void
     */
    public function setLoanTerm($months)
    {
        $this->loan_term = is_numeric($months) ? $months : 0;
    }

    /** 
     * Sets $loan_amount to a new value
     * 
     * @param int $amount a loan amount
     * @return void
     */
    public function setLoanAmount($amount)
    {
        $this->loan_amount = is_numeric($amount) ? $amount : 0;
    }

    /** 
     * Sets $loan_start_timestamp by input month and year
     * 
     * @param int $month a loan start month
     * @param int $year a loan start year
     * @return void
     */
    public function setLoanStart($month, $year)
    {
        try {
            $this->loan_start_timestamp = new DateTime($year.'-'.$month.'-01 00:00:00');
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
    }

    abstract public function getMonthlyPayments();
    abstract public function getPaymentPlan();
    abstract public function getTotalPayment();
    abstract public function getTotalInterest();
}

class HousingLoan extends Loan
{
    /** 
     * Get monthly payments
     * 
     * @return int the monthly payments
     */
    public function getMonthlyPayments()
    {
        if(!$this->isCalculated) {
            $this->calculateMonthlyPayments();
        }
        return $this->monthly_payments;
    }

    /** 
     * Get payment plan
     * 
     * @return array the payment plan
     */
    public function getPaymentPlan()
    {
        if(!$this->isCalculated) {
            $this->calculateMonthlyPayments();
        }
        return $this->payment_plan;
    }

    /** 
     * Get total payment
     * 
     * @return int the total payment
     */
    public function getTotalPayment()
    {
        if(!$this->isCalculated) {
            $this->calculateMonthlyPayments();
        }
        return $this->total_payment;
    }

    /** 
     * Get total interest
     * 
     * @return int the total interest
     */
    public function getTotalInterest()
    {
        if(!$this->isCalculated) {
            $this->calculateMonthlyPayments();
        }
        return $this->total_interest;
    }

	/** 
     * Calculate monthly payments
     * 
     * @return void
     */
	private function calculateMonthlyPayments()
    {
        $amount = $this->loan_amount;
        $rate = $this->interest_rate;
        $months = $this->loan_term;

        // 1. Monthly payments
        if($amount > 0 && $rate > 0 && $months > 0) {
            $i = $rate/1200; // Periodic Interest Rate
            $pow_i = pow((1+$i), $months);
            $discount_factor = ($pow_i-1)/($i*$pow_i);
            $this->monthly_payments = $amount/$discount_factor;
        } else {
        	$this->monthly_payments = 0;
        }

        // 2. Payment plan
        $payment_plan = array();
        $total_interest = 0;
        $current_balance = $amount;
        $current_date = $this->loan_start_timestamp;
        for($j=0; $j<$months; $j++) {
            $interest = $i * $current_balance;
            $principal = $this->monthly_payments - $interest;
            $current_balance -= $principal;
            $payment_plan[] = array(
                'date' => $current_date->format('M, Y'),
                'principal' => $principal,
                'interest' => $interest,
                'balance' => ($current_balance < 0) ? 0 : $current_balance
            );
            $current_date->modify('first day of next month');
            $total_interest += $interest;
        }
        $this->payment_plan = $payment_plan;

        // 3. Total interest
        $this->total_interest = $total_interest;

        // 4. Total payments
        $this->total_payment = $amount+$total_interest;

        // Calculation complete
        $this->isCalculated = true;
    }
}
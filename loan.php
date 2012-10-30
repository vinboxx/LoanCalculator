<?php require('lib/loan.class.php');

	function getHousingLoanMonthlyPayments()
	{
		$loan_amount = isset($_GET['loanAmount']) ? $_GET['loanAmount'] : NULL;
		$loan_term = isset($_GET['loanTerm']) ? $_GET['loanTerm'] : NULL;
		$interest_rate = isset($_GET['interestRate']) ? $_GET['interestRate'] : NULL;
		$loan_start_month = isset($_GET['loanStartMonth']) ? $_GET['loanStartMonth'] : NULL;
		$loan_start_year = isset($_GET['loanStartYear']) ? $_GET['loanStartYear'] : NULL;
		$error_messages = array();
		$result = array();

		if(!is_numeric($loan_amount)) {
			$error_messages['loan_amount'] = 'Loan amount is not valid';
		}
		if(!is_numeric($loan_term)) {
			$error_messages['loan_term'] = 'Loan term is not valid';
		}
		if(!is_numeric($interest_rate)) {
			$error_messages['interest_rate'] = 'Interest rate is not valid';
		}

		if(count($error_messages)) {
			$result['status'] = 'ERROR';
			$result['error_messages'] = $error_messages;
		} else {
			$housing_loan = new HousingLoan();
			$housing_loan->setLoanAmount($loan_amount);
			$housing_loan->setInterestRate($interest_rate);
			$housing_loan->setLoanTerm($loan_term*12);
			$housing_loan->setLoanStart($loan_start_month, $loan_start_year);
			$monthly_payments = $housing_loan->getMonthlyPayments();
			if(is_nan($monthly_payments)) {
				$result['status'] = 'ERROR';
				$result['error_messages'] = array('monthly_payments'=>'The result number is too large');
			} else {
				$result['status'] = 'OK';
				$result['data'] = array(
					'monthly_payments' => $monthly_payments,
					'payment_plan' => $housing_loan->getPaymentPlan(),
					'total_payment' => $housing_loan->getTotalPayment(),
					'total_interest' => $housing_loan->getTotalInterest()
					);
			}
		}

		echo json_encode($result);
	}

	$action = isset($_GET['action']) ? $_GET['action'] : NULL;

	if($action && function_exists($action)) {
		call_user_func($action);
	}
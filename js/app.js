Number.prototype.formatMoney = function(decPlaces, thouSeparator, decSeparator) {
  var n = this,
  decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
  decSeparator = decSeparator == undefined ? "." : decSeparator,
  thouSeparator = thouSeparator == undefined ? "," : thouSeparator,
  sign = n < 0 ? "-" : "",
  i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
  j = (j = i.length) > 3 ? j % 3 : 0;
  return sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
};

var LoanCal = {
  init: function () {
    LoanCal.activeCurrentDate();
    $('#loan_form').submit(function (e){
      e.preventDefault();
      LoanCal.getHousingLoanMonthlyPayments();
    });
  },
  activeCurrentDate: function () {
    var d = new Date()
     , currentYear = d.getFullYear()
     , maxYear = currentYear + 15
     , minYear = currentYear - 15
     , options = []
     , option

    // Month
    $('#loan_start_month option').eq(d.getMonth()).attr('selected', 'selected');

    // Year
    for(i=minYear; i<=maxYear; i++) {
      option = $('<option>').val(i).text(i);
      if(i == currentYear) {
        option.attr('selected', 'selected');
      }
      options.push(option);
    }
    $('#loan_start_year').append(options);
  },
  getHousingLoanMonthlyPayments: function () {
    var error_el = $('.error-messages')
      , result_el = $('.result-box')
      , monthly_el = $('.monthly-payments')
      , payment_plan_table_el = $('#payment_plan')
      , payment_plan_template_el = $('#monthly_template', payment_plan_table_el).eq(0).clone().removeAttr('id').show()
      , new_template
      , decimal_length = 2

    $.ajax({
      type: 'GET',
      url: 'loan.php',
      dataType: 'json',
      data: {
        action: 'getHousingLoanMonthlyPayments',
        loanAmount: $('input[name="loanAmount"]').val(),
        interestRate: $('input[name="interestRate"]').val(),
        loanTerm: $('input[name="loanTerm"]').val(),
        loanStartMonth: $('#loan_start_month').val(),
        loanStartYear: $('#loan_start_year').val()
      },
      beforeSend: function () {
        $('#btn_calculate').hide();
        $('.calculating').show();
        error_el.removeClass('fade-in');
      },
      success: function (res) {
        $("html, body").animate({ scrollTop: 0 }, 500);
        if(res.status === 'OK') {
          error_el.hide();

          var payment_plan = res.data.payment_plan
            , monthly_payments = res.data.monthly_payments.formatMoney()
            , total_payment = res.data.total_payment.formatMoney()
            , total_interest = res.data.total_interest.formatMoney()
          
          monthly_el.text(monthly_payments);
          $('.monthly-row', payment_plan_table_el).not('#monthly_template').remove();
          $('.number-of-payments', result_el).text(payment_plan.length);
          $('.total-payment', result_el).text(total_payment);
          $('.total-interest', result_el).text(total_interest);
          $('.payoff-date', result_el).text(payment_plan[payment_plan.length-1].date);
          
          for(i in payment_plan) {
            new_template = payment_plan_template_el.clone();
            $('.monthly-date', new_template).text(payment_plan[i].date);
            $('.monthly-principal', new_template).text(payment_plan[i].principal.formatMoney());
            $('.monthly-interest', new_template).text(payment_plan[i].interest.formatMoney());
            $('.monthly-balance', new_template).text(payment_plan[i].balance.formatMoney());
            new_template.appendTo(payment_plan_table_el);
          }

          $('.result', result_el).show();
        } else {
          console.log('ERROR', res);
          $('.result', result_el).hide();
          error_el.empty();
          $.each(res.error_messages, function (key, message) {
            error_el.append('<li>'+message+'</li>');
          });
          error_el.slideDown().addClass('fade-in');
        }
        result_el.slideDown().addClass('open');
        $('.calculating').hide();
        $('#btn_calculate').fadeIn();
      },
      error: function (res) {
          $('.result', result_el).hide();
          error_el.html('<li>'+res.statusText+'</li>');
          error_el.slideDown().addClass('fade-in');
      }
    });
  }
}
$(document).ready(LoanCal.init);
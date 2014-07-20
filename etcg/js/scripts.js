// off-canvas nav toggle
$(document).ready(function () {
  $('[data-toggle="offcanvas"]').click(function () {
    $('.row-offcanvas').toggleClass('active');
	$('[data-toggle="offcanvas"] i').toggleClass( "fa-chevron-circle-right fa-times" )
  });
});

// initialize tooltips
$('[data-toggle="tooltip"]').tooltip();

// initialize trades and cards panel collapse
$('.collapse').collapse({
	toggle: false
});

// trades and cards collapse toggle handlers
$('.collapse').on('hide.bs.collapse', function () {
	$('[data-toggle="collapse"][data-target="#' + $(this).attr('id') + '"] span i').toggleClass( "fa-chevron-down fa-chevron-up" );
	$(this).closest('.panel').toggleClass( "panel-default panel-primary" );
});

$('#trades-panel .collapse').on('show.bs.collapse', function () {
	$('.collapse').not($(this)).collapse('hide');
	$('[data-toggle="collapse"][data-target="#' + $(this).attr('id') + '"] span i').toggleClass( "fa-chevron-down fa-chevron-up" );
	$(this).closest('.panel').toggleClass( "panel-default panel-primary" );
});

$('#cards-panel .collapse').on('show.bs.collapse', function () {
	$('[data-toggle="collapse"][data-target="#' + $(this).attr('id') + '"] span i').toggleClass( "fa-chevron-down fa-chevron-up" );
	$(this).closest('.panel').toggleClass( "panel-default panel-primary" );
});

// trades view filters
$('#trades-tcg-sel, #trades-type-sel').change(function(){
	$('.collapse').collapse('hide');
	
	if ( $('#trades-tcg-sel').val() != "All TCGs" ) {
		$('.new-trade-btn').attr('href','newtrade.php?id=' + $('#trades-tcg-sel').find('option:selected').attr('data-tcg-id'));
		
		if ( $('#trades-type-sel').val() == "All Trades" ) {
			$('.panel[data-trade-tcg="' + $('#trades-tcg-sel').val() + '"]').show(); $('.panel:not([data-trade-tcg="' + $('#trades-tcg-sel').val() + '"])').hide();
		}
		else if ( $('#trades-type-sel').val() == "Incoming" ) {
			$('.panel[data-trade-tcg="' + $('#trades-tcg-sel').val() + '"][data-trade-type="incoming"]').show(); $('.panel:not([data-trade-tcg="' + $('#trades-tcg-sel').val() + '"][data-trade-type="incoming"])').hide();
		}
		else if ( $('#trades-type-sel').val() == "Outgoing" ) {
			$('.panel[data-trade-tcg="' + $('#trades-tcg-sel').val() + '"][data-trade-type="outgoing"]').show(); $('.panel:not([data-trade-tcg="' + $('#trades-tcg-sel').val() + '"][data-trade-type="outgoing"])').hide();
		}
	}
	else if ( $('#trades-tcg-sel').val() == "All TCGs" ) {
		$('.new-trade-btn').attr('href','newtrade.php');
		
		if ( $('#trades-type-sel').val() == "All Trades" ) {
			$('.panel').show();
		}
		else if ( $('#trades-type-sel').val() == "Incoming" ) {
			$('.panel[data-trade-type="incoming"]').show(); $('.panel:not([data-trade-type="incoming"])').hide();
		}
		else if ( $('#trades-type-sel').val() == "Outgoing" ) {
			$('.panel[data-trade-type="outgoing"]').show(); $('.panel:not([data-trade-type="outgoing"])').hide();
		}
	}
});

// initialize filtered view
$('#trades-tcg-sel, #trades-type-sel').trigger('change');

// event handler for new field buttons (trade form)
$('.btn-new-trading, .btn-new-receiving').on('click', function(){
	if ( $(this).hasClass('btn-new-trading') ) {
		var tdContainer = $(this).closest('.panel-body').find('.td-trading-cats');
	}
	else {
		var tdContainer = $(this).closest('.panel-body').find('.td-receiving-cats');
	}
	var newFields = tdContainer.find('div.clearfix').eq(0).clone();
	newFields.find('input').attr('value',null);
	newFields.find('input').val(null);
	newFields.find('select option').attr('selected',null);
	newFields.appendTo(tdContainer);
});

// event handler for new field button (easy update form)
$('.btn-new-cards').on('click', function(){
	var container = $(this).closest('.form-group');
	var newFields = container.find('div.row').eq(0).clone();
	newFields.find('input').val(null);
	newFields.find('select option').attr('selected',null);
	newFields.appendTo(container);
});

// Disable "List Cards" option if "Grab" option is selected (for new colelcting and mastered decks forms)
$('#new-collecting-modal form input[name=findcards], #new-mastered-modal form input[name=findcards]').on('change',function(){
	var theForm = $(this).closest('form');
	if ( $(this).prop('checked') ) { theForm.find('input[name=cards]').prop('disabled', true) }
	else { theForm.find('input[name=cards]').prop('disabled', null) }
});

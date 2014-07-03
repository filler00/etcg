function collapseTrades(tradeId,triggerId) {
	var trade = document.getElementById(tradeId);
	var trigger = document.getElementById(triggerId);
	var rows = trade.getElementsByClassName('collapse');
	
	var allRows = document.getElementsByClassName('collapse');
	var allLinked = document.getElementsByClassName('linked');
	
	for (i=0; i<allRows.length; i++) {
		allRows[i].style.display = "none";
	}
	
	for (i=0; i<allLinked.length; i++) {
		allLinked[i].getElementsByTagName('td')[0].className = "top";
	}
	
	for (i=0; i<rows.length; i++) {
		if (rows[i].style.display == "none") {
			rows[i].style.display = "table-row";
			trigger.getElementsByTagName('td')[0].className = "topSelect";
		}
		else {
			rows[i].style.display = "none";
		}
	}
}

function navTrades(show,hide) {
	showDiv = document.getElementById(show);
	hideDiv = document.getElementById(hide);
	showDiv.style.display = "block";
	hideDiv.style.display = "none";
	
	document.getElementById(show + "Tab").className = "tradesNavSel";
	document.getElementById(hide + "Tab").className = "tradesNavDesel";
}

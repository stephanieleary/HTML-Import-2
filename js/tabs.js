jQuery(document).ready(function($) {
	// Set up jQuery UI tabs
	$('.ui-tabs').tabs();
	
	// Show/hide table rows based on selected radio value
	$("input.showrow").click(function() {
	        var tag = $(this).val();
			var importing = $(this).attr('title');
			var rowID = $(this).parents("tr").attr("id");
			//console.log("#"+rowID+" tr#"+importing+"-"+tag);
	        $("#"+rowID+" tr#"+importing+"-region").hide();
			$("#"+rowID+" tr#"+importing+"-tag").hide();
	        $("#"+rowID+" tr#"+importing+"-"+tag).show();
	    });
		
	// Show/hide table rows based on checkbox toggle			
	$("input.toggle").click(function() {
	        var tr = this.id;
			$("tr."+tr).toggle();
	    });
	
	// Clone table rows
	$(".cloneTableRows").live('click', function(){
		var thisTableId = $(this).parents("table").attr("id");
		var lastRow = $('#'+thisTableId + " tbody tr.clone:last");
		var oldID = lastRow.attr("id");
		var newID = oldID.replace('customfield','');
		var prevID = newID; // need oldID later
		newID = newID.valueOf(); // (int)
		newID++;
		var newRow = lastRow.clone(true);
		newRow.attr( "id", 'customfield'+newID );		
		newRow.find(':input')
		    .attr('name', function(index, name) {
		          return name.replace(prevID, newID)
			});
		newRow.find('input[type=text]').val('');
		newRow.find('a.delRow').removeClass('hidden');
		$('#'+thisTableId).append(newRow);	
		return false;
	});

	// Delete a table row
	$(".delRow").click(function(){
		$(this).parents("tr").remove();
		return false;
	});
});
document.addEventListener("DOMContentLoaded", function(){
	var $ = jQuery;
	$('.taxonomy-ajax').each(function(){
		var $this = $(this);
		$this.select2({
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				data: function (params) {
				  	return {
					    q: params.term,
				    	action: 'taxonomy_ajax_ac',
				    	taxonomy: $this.data('taxonomy'),
				    	parent_only: $this.data('parent_only')
				  	};
				},
				processResults: function (data) {
				  	return{
					  results: data
					};
				},
				cache: true
			},
			minimumInputLength: 3,
		});

		if( $this.data('sortable') == true ){
			$this.select2Sortable({
				bindOrder: 'sortableStop',
				sortableOptions: { placeholder: 'ui-state-highlight' }
			});

			$this.on('change',function() {
				$this.select2SortableOrder();
			});
		}
	});
});

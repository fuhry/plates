$(function()
	{
		// editable stars
		$('.stars.editable').each(function(i, e)
			{
				$(e).mousemove(function(e)
					{
						var offs = $(this).offset();
						var myval = 8 * (Math.round((e.pageX - offs.left) / 8));
						$('.stars-inner', this).css('width', myval);
						$(this).next('.stars-label').text(Math.round(myval / 8, 1) / 2);
					}).mouseout(function(e)
					{
						var offs = $(this).offset();
						
						if ( e.pageX >= offs.left && e.pageY >= offs.top && e.pageX <= offs.left + ($(this).attr('data-size') * 16) && e.pageY <= offs.top + 16 )
						{
							// don't do this function if we're still inside the element
							return;
						}
						
						$(this).next('.stars-label').text('');
						$('.stars-inner', this).css('width', Number($(this).next().next().val()) * 16);
					}).click(function(e)
						{
							var offs = $(this).offset();
							var myval = 8 * (Math.round((e.pageX - offs.left) / 8));
							$(this).next().next().val(Math.round(myval / 8, 1) / 2);
						}).mouseout();
					
			});
		if ( $('#schema-create-form').length > 0 )
		{
			$('#schema-create-attr-type-select').change(function()
				{
					refresh_attr_opts();
				});
			refresh_attr_opts();
		}
		$('button.btn.radio-append').click(function()
			{
				$(this.previousSibling).before('<input type="text" name="attr[plugin][Radio][a_options][options][]" value="" />  <button class="btn btn-danger btn-mini remove-radio-item"><i class="icon-trash icon-white"></i></button><br /><br />');
				var e = this;
				while ( !e.tagName || e.tagName != 'INPUT' )
					e = e.previousSibling;
				e.focus();
				setup_radio_removers();
				return false;
			});
		setup_radio_removers();
		$('i.icon-resize-vertical').css('cursor', 'move').click(function() { return false; });
		$('table.schema-table tbody').sortable({handle: 'i.icon-resize-vertical', forceHelperSize: true, stop: function(event, ui)
				{
					var ids = [];
					$('table.schema-table tbody tr').each(function(i, e)
						{
							ids.push($(e).attr('data-attrid'));
						});
					$.post('schema.php?act=update_sort_order', 'order=' + ids.join(','), function(response, xhr)
						{
						}, 'json');
				}
			});
	});

function refresh_attr_opts()
{
	var select_me = $('#schema-create-attr-type-select').val();
	$('div.schema_copts').hide();
	$('div.schema_copts.' + select_me).show();
}

function setup_radio_removers()
{
	$('button.btn.remove-radio-item').click(function()
		{
			$(this.previousSibling).remove();
			$(this.previousSibling).remove();
			$(this.nextSibling).remove();
			$(this.nextSibling).remove();
			$(this).remove();
			return false;
		});
}

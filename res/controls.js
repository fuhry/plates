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
						});
				
			});
	});

(function()
{
	CKEDITOR.plugins.charcounter =
	{
	};
	
	var plugin = CKEDITOR.plugins.charcounter;

	CKEDITOR.plugins.add('charcounter',
	{
		init: function(editor)
		{
			if('undefined' == typeof editor.config.counter)
			{
				return false;
			}
			
			var counter_form = document.getElementById(editor.config.counter);
			var locked;

			if(!counter_form)
			{
				return false;
			}

			// init counter form
			counter_form.readOnly = true;
			counter_form.size = '3';
			counter_form.maxLength = '3';
			
			editor.on("instanceReady", function()
			{
				var currentLength = editor.getData().replace(/(<([^>]+)>)/ig,"").replace(/&[a-z0-9]*;/ig, "-").replace(/\n/ig, "").replace(/\s+/g, " ").length;
				counter_form.innerHTML = currentLength;
				
				this.document.on('keyup', function(evt)
				{
					var sauber = editor.getData().replace(/(<([^>]+)>)/ig,"").replace(/&[a-z0-9]*;/ig, "-").replace(/\n/ig, "").replace(/\s+/g, " ");
					var currentLength = sauber.length;
					counter_form.innerHTML = currentLength;
				});
			});
		}
	});
})();
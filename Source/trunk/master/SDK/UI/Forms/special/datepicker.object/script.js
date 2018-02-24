var jq = jQuery.noConflict();

/*
*  On Value change fill the appropriate inputs
*/

// Day
jq(document).on('change', 'select[name = "dayPicker"]',  function() {	
	getPopupDate(this, false);
});
// Month Changes
jq(document).on('change', 'select[name = "monthPicker"]',  function() {
	getPopupDate(this, true);
});

// Year Changes
jq(document).on('keyup', 'input[name = "yearPicker"]',  function() {	
	getPopupDate(this, true)
});

jq(document).on('keyup', 'input[name = "date"]', function() {
	var contextId = jq(this).closest('.datePickerWrapper').attr('id');
	
	var date = jq(this).val();	
	
	//Declare Regex 
	var regex = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
	var dateArray = date.match(regex);
	
	if (dateArray == null)
	{
		setError(contextId);
		return false;
	}
	
	var validate = validateDate(dateArray[1], dateArray[3], dateArray[5]);
	if(!validate)
	{
		//show error
		setError(contextId);
	}
	else
		clearError(contextId);
});

				
jq(document).on('click', '[data-dppopup="show"]', function(ev) {
						jq(this).popup.type = "obedient toggle";
						jq(this).popup.position = 'bottom|right';
						
						var id = jq(ev.target).closest('.datePickerWrapper').attr('id');
						var popupContent = jq('#'+id+' .pickerPopup').clone(true);
						popupContent.removeClass('noDisplay')
						jq(this).popup(popupContent);
					});
					
// Change main visible input
function setDate(contextId, day, month, year) 
{	
	jq('#' + contextId).find('input[name = "'+ contextId +'_month"]').val(month);
	jq('#' + contextId).find('input[name = "'+ contextId +'_year"]').val(year);
	
	
	
	
	jq('#' + contextId).find('input[name = "'+ contextId +'_day"]').val(day);
	
	var date = day + "/" + month + "/" + year;
	jq('#' + contextId).find('input[name = "date"]').text(date);
	jq('#' + contextId).find('input[name = "date"]').val(date);
	
	var validate = validateDate(day, month, year);
	if(!validate)
	{
		//show error
		setError(contextId);
	}
	else
		clearError(contextId);
} 
	
function getPopupDate(object, rebuild)
{
	var contextId = jq(object).closest('.pickerPopup').find('input[name="id"]').attr('value');	
	var month = parseInt(jq(object).closest('.pickerPopup').find('select[name = "monthPicker"]').val(), 10);
	var year = parseInt(jq(object).closest('.pickerPopup').find('input[name = "yearPicker"]').val(), 10);
	
	if(rebuild)
	{
		var elem = jq(object).closest('.pickerPopup').find('select[name = "dayPicker"]')
		if(elem.prop) 
		{
			var options = elem.prop('options');
		}
		else
		{
			var options = elem.attr('options');
		}
		
		if (month == 4 || month ==6 || month ==9 || month ==11)
		{
			// 30 days
			if(options.length != 30)
			{
				elem.html('');
				for(var i = 1; i <= 30; i++)
				{
					options[options.length] = new Option(i, i);
				}
			}
		}
		else if (month == 2)
		{
			var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
			var max = 28;
			if (isleap)			
			{
				max = 29;			
			}
			// 29 days
			if(options.length != max)
			{
				elem.html('');
				for(var i = 1; i <= max; i++)
				{
					options[options.length] = new Option(i, i);
				}
			}
		}
		else
		{
			// 31 days
			if(options.length != 31)
			{
				elem.html('');
				for(var i = 1; i <= 31; i++)
				{
					options[options.length] = new Option(i, i);
				}
			}
		}
	}
	
	var day = parseInt(jq(object).closest('.pickerPopup').find('select[name = "dayPicker"]').val(), 10);
	setDate(contextId, day, month, year);
}	
				
function validateDate(day, month, year)
{	
	if (month < 1 || month > 12)
		return false;
	else if (day < 1 || day > 31)
		return false;
	else if ((month ==4 || month ==6 || month ==9 || month ==11) && day ==31)
		return false;
	else if (month == 2)
	{
		var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day == 29 && !isleap))
			return false;
	}
	return true;
}	

function setError(contextId)
{
	jq('#' + contextId).find('input[name = "date"]').addClass('input-validation-error');
}

function clearError(contextId)
{
	jq('#' + contextId).find('input[name = "date"]').removeClass('input-validation-error');
}
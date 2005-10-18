function addEvent(obj, evType, fn)
{
	if (obj.addEventListener)
	{
		obj.addEventListener(evType, fn, true);
		return true;
	}
	else if (obj.attachEvent)
	{
		var r = obj.attachEvent("on" + evType, fn);
		return r;
	}
	else
	{
		return false;
	}
}

function expandCollapse()
{
	for (var i = 0; i < expandCollapse.arguments.length; i++)
	{
		var element = document.getElementById(expandCollapse.arguments[i]);
		element.style.display = (element.style.display == "none") ? "block" : "none";
	}
}

function toggleCheckbox(form, action, checked)
{
	for (i = 0; form.elements[i]; i++)
	{
		el = form.elements[i];
		if (el.type == 'checkbox')
		{
			if (checked)
				el.checked = true;
			else
				el.checked = false;
		}
	}
}

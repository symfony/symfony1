var Page_ValidationVer = "2.00";

var _val_agt=navigator.userAgent.toLowerCase();
var _val_is_major=parseInt(navigator.appVersion);
var _val_is_ie=((_val_agt.indexOf("msie")!=-1) && (_val_agt.indexOf("opera")==-1));
var _val_isNT=_val_agt.indexOf("windows nt")!=-1;
var _val_IE=(document.all);
var _val_IE4=(_val_is_ie && (_val_is_major==4) && (_val_agt.indexOf("msie 4")!=-1));
var _val_IE6=(_val_is_ie && (_val_agt.indexOf("msie 6.0")!=-1));
var _val_NS=(document.layers);
var _val_DOM=(document.getElementById);
var _val_isMac=(_val_agt.indexOf("Mac")==-1);
var _val_allString="document.";
_val_allString += (_val_IE)?"all['":(_val_DOM)?"getElementById(\"":"";
var _val_styleString=(_val_IE)?".style":(_val_DOM)?"\").style":"";
var _val_endAllString=(_val_DOM && !_val_IE)?"\")":"']";
var _val_px=(_val_DOM)?"px":"";

var Page_DomValidationVer = "2";
var Page_IsValid = true;
var Page_BlockSubmit = false;
var CausesValidation = false;

var Page_Validators_tmp = Array();

function Validator_addEvent(obj, evType, fn, useCapture){
  if (obj.addEventListener){
    obj.addEventListener(evType, fn, useCapture);
    return true;
  } else if (obj.attachEvent){
    var r = obj.attachEvent('on'+evType, fn);
    return r;
  }
}

function ValidatorUpdateDisplay(val) {
	var prop = dom_getAttribute(val,"display");

	var style_str = "", style_prefix = "display: ";

	var classname = dom_getAttribute(val,"controlcssclass");
	if(typeof(classname) == "string")
	{
		var obj = dom_getElementByID(dom_getAttribute(val,"controltovalidate"));
		if(typeof(obj) != "undefined")
		{
			if(!val.isvalid)
				obj.className += " "+classname;	
			else
			{
				var reg = new RegExp("\\s?"+classname); 
				obj.className = obj.className.replace(reg, '');
			}
		}
	}

    if (typeof(prop) == "string") {
        if (prop == "None") {
            return;
        }
        if (prop == "Dynamic") {
			style_str = val.isvalid ? "none" : "inline";
            val.style.display = style_str;
            return;
        }
    }
    val.style.visibility = val.isvalid ? "hidden" : "visible";
}

function ValidatorUpdateIsValid() {
    var i;
    for (i = 0; i < Page_Validators.length; i++) {
        if (!Page_Validators[i].isvalid) {
            Page_IsValid = false;
            Page_BlockSubmit = true;
            return;
        }
   }
   Page_IsValid = true;
}

function ValidatorHookupControl(control, val) {
    if (control != null)
    {
	   if (typeof(control.Validators) == "undefined") {
            control.Validators = new Array;
	        var ev = control.onchange;
	        var new_ev;
            if (typeof(ev) == "function" ) {
                ev = ev.toString();
                new_ev = "if (Page_IsValid || Page_BlockSubmit) {" + ev.substring(ev.indexOf("{") + 1, ev.lastIndexOf("}")) + "}";
            }
            else {
                new_ev = "";
            }

            var func = new Function("ValidatorOnChange('" + control.id + "'); " + new_ev);
	        Validator_addEvent(control, 'change', func);
			//control.onchange = func;
	    }
        control.Validators[control.Validators.length] = val;
    }
}

function ValidatorGetValue(id) {
    var control;
    control = dom_getElementByID(id);
    if (control == null)
		return "";

    if (typeof(control.value) == "string") {
        return control.value;
    }

    if (typeof(control.tagName) == "undefined" && typeof(control.length) == "number") {
        var j;
        for (j=0; j < control.length; j++) {
            var inner = control[j];
            if (typeof(inner.value) == "string" && (inner.type != "radio" || inner.status == true)) {
                return inner.value;
            }
        }
    }
}

function Page_ClientValidate(e) {
    var i,ctrl,obj;

	if(window.event)
		obj = window.event.srcElement;
	else if( e != null )
		obj = e.target;

	if(typeof(obj) != "undefined"){
		toggleGroups(obj);
	}

    for (i = 0; i < Page_Validators.length; i++) {
        ValidatorValidate(Page_Validators[i]);
    }
    ValidatorUpdateIsValid();
    ValidationSummaryOnSubmit();
    Page_BlockSubmit = !Page_IsValid;
	CausesValidation = true;
    return Page_IsValid;
}

function ValidatorCommonOnSubmit() {
///<V1.200> - Support for CausesValidation property
   //var retValue = !Page_BlockSubmit;
	var retValue = !CausesValidation;

	if(CausesValidation)
		retValue = !Page_BlockSubmit;

	//alert("block = " + Page_BlockSubmit + "; causes = "+CausesValidation);

   if (!_val_NS) {   // If we are not in crappy old Netscape 4.7 then....
      if (_val_IE && _val_is_ie)  // If its Internet Explorer, set our return event value.
         event.returnValue = retValue;
   }

   Page_BlockSubmit = false;

   return retValue;
}

function ValidatorOnChange(controlID) {
    var cont = dom_getElementByID(controlID);
    var vals = cont.Validators;
    var i;
    for (i = 0; i < vals.length; i++) {
        ValidatorValidate(vals[i]);
    }
    ValidatorUpdateIsValid();
	CausesValidation = false;
    return Page_IsValid;
}

function ValidatorValidate(val) {
    val.isvalid = true;
	var enabled =  dom_getAttribute(val, 'enabled');
    if (enabled == null || val.enabled != false)    // V2.00 change
    {
        if (typeof(val.evalfunc) == "function") {
            val.isvalid = val.evalfunc(val);
        }
    }
    ValidatorUpdateDisplay(val);
}


function toggleGroups(controller)
{
	if(typeof(Validator_Groups) == "undefined")
		return;

	//remember the default list of validators
	if(Page_Validators_tmp.length == 0)
		Page_Validators_tmp = Page_Validators;

	var found = false;

	//go through each of the groups
	for(var i in Validator_Groups) {
		for (var obj in Validator_Groups[i]) {
			var members = Validator_Groups[i][obj];

			//the object clicked == the group
			if(obj== controller.id) {

				//make all the validators valid.
				Page_Validators = Page_Validators_tmp;
				for (i = 0; i < Page_Validators.length; i++) {
					Page_Validators[i].isvalid = true;
					ValidatorUpdateDisplay(Page_Validators[i]);
				}

				Page_Validators = new Array();


				for(var k in members)
					Page_Validators.push(document.getElementById(members[k]));

				found = true;
				break;
			}
		}
	}

	if(found == false)
		Page_Validators = Page_Validators_tmp;

	ValidatorOnLoad()
}

function ValidatorOnLoad() {
    if (typeof(Page_Validators) == "undefined")
        return;

    var i, val;
    for (i = 0; i < Page_Validators.length; i++) {
        val = Page_Validators[i];
        var evalFunction = dom_getAttribute(val,"evaluationfunction");
        if (typeof(evalFunction) == "string") {
            eval("val.evalfunc = " + evalFunction + ";");
        }
        var isValidAttribute = dom_getAttribute(val,"isvalid");
        if (typeof(isValidAttribute) == "string") {
            if (isValidAttribute == "False") {
                val.isvalid = false;
                Page_IsValid = false;
            }
            else {
                val.isvalid = true;
            }
        } else {
            val.isvalid = true;
        }
        if (typeof(val.enabled) == "string") {
            val.enabled = (val.enabled != "False");
        }
        var controlToValidate = dom_getAttribute(val,"controltovalidate");
        if (typeof(controlToValidate) == "string") {
			ValidatorHookupControl(dom_getElementByID(controlToValidate), val);
        }
		var controlhookup = dom_getAttribute(val,"controlhookup");
		if (typeof(controlhookup) == "string") {
            if (controlhookup != "")    // V2.00 Change
            {
			    ValidatorHookupControl(dom_getElementByID(controlhookup), val);
			}
		}
    }
    Page_ValidationActive = true;
    if (!Page_IsValid)
		ValidationSummaryOnSubmit();

	// IE4 hack test
    if (_val_IE4)
    {
		var ev = new Function("ValidationSummaryOnSubmit();");
		document.onreadystatechange=ev;
	}

}

function RegularExpressionValidatorEvaluateIsValid(val) {
    var value = ValidatorGetValue(dom_getAttribute(val, "controltovalidate"));
    if (value == "")
        return true;
    var rx = new RegExp(dom_getAttribute(val, "validationexpression"));
    var matches = rx.exec(value);
    return (matches != null && value == matches[0]);
}

function ValidatorTrim(s) {
    // change sent by Mathew A. Frank 11/05/2003 <V2.000> fix.
    return s.replace(/^\s+|\s+$/g, "");
}

function RequiredFieldValidatorEvaluateIsValid(val) {
	var id = dom_getAttribute(val, "controltovalidate");
	var a = ValidatorTrim(ValidatorGetValue(id));
	var b = ValidatorTrim(dom_getAttribute(val, "initialvalue"));
	obj = dom_getElementByID(id);
	if(obj.type == 'checkbox')
		return obj.checked
	else
	    return (a != b);
}

//add for checking how many selections are made
function RequiredListValidatorEvaluateIsValid(val)
{
	var controlName = dom_getAttribute(val, "controltovalidate");
	var min = dom_getAttribute(val, "min");
	var max = dom_getAttribute(val, "max");
	min = min == '-INF' ? Number.NEGATIVE_INFINITY : min;
	max = max == 'INF' ? Number.POSITIVE_INFINITY : max;
	var elements = dom_getElementsByName(controlName);

	var requiredString = dom_getAttribute(val, "required");
	var required = new Array();
	if(requiredString.length > 0)
		required = requiredString.split(/,\s*/);
	
	//alert(required.length);
	if(elements.length > 0)
	{
		//alert(elements[0].type);
		switch(elements[0].type)
		{
			case 'radio':
			case 'checkbox':
				return IsValidRadioList(elements, min, max, required);
			case 'select-multiple':
				return IsValidSelectMultipleList(elements, min, max, required);
			default: return true;
		}
	}
	else
		return true;
}

//array exists function
function validation_array_exists(array, element)
{
	for(var i in array)
	{
		var type = typeof(array[i]);
		if(type == 'string' || type == 'number')
		{
			if(array[i] == element) return true;
		}			
	}
	return false;
}

//radio group selection
function IsValidRadioList(elements, min, max, required)
{
	var checked = 0;
	var values = new Array();
	for(var i = 0; i < elements.length; i++)
	{
		if(elements[i].checked)
		{
			checked++;
			values.push(elements[i].value);
		}
	}
	return IsValidList(checked, values, min, max, required);
}

//multiple selection check
function IsValidSelectMultipleList(elements, min, max, required)
{
	var checked = 0;
	var values = new Array();
	for(var i = 0; i < elements.length; i++)
	{
		var selection = elements[i];
		for(var j = 0; j < selection.options.length; j++)
		{
			if(selection.options[j].selected)
			{
				checked++;
				values.push(selection.options[j].value);
			}
		}
	}
	return IsValidList(checked, values, min, max, required);
}

//check if the list was valid
function IsValidList(checkes, values, min, max, required)
{
	var exists = true;

	if(required.length > 0)
	{
		//required and the values must at least be have same lengths
		if(values.length < required.length)
			return false;
		for(var k = 0; k < required.length; k++)
			exists = exists && validation_array_exists(values, required[k]);
	}
	
	return exists && checkes >= min && checkes <= max;
}

///////////////////////////////////// my stuff ////////////////////////////////////////////////////////////

function ValidatorCompare(operand1, operand2, operator, val) {
    var dataType = dom_getAttribute(val, "type");
    var op1, op2;
    if ((op1 = ValidatorConvert(operand1, dataType, val)) == null)
        return false;
    if (operator == "DataTypeCheck")
        return true;
    if ((op2 = ValidatorConvert(operand2, dataType, val)) == null)
        return true;
    if (op2 == "")
		return true;
    switch (operator) {
        case "NotEqual":
            return (op1 != op2);
        case "GreaterThan":
            return (op1 > op2);
        case "GreaterThanEqual":
            return (op1 >= op2);
        case "LessThan":
            return (op1 < op2);
        case "LessThanEqual":
            return (op1 <= op2);
        default:
            return (op1 == op2);
    }
}



function CompareValidatorEvaluateIsValid(val) {
    var ctrl = dom_getAttribute(val, "controltovalidate");
    if (null == ctrl)
        return true;
    var value = ValidatorGetValue(ctrl);
    if (ValidatorTrim(value).length == 0)
        return true;
    var compareTo = "";

    // V2.0 changes
    var hookupCtrl = dom_getAttribute(val, "controlhookup");
    var useCtrlToValidate = false;
    if (hookupCtrl != null)
    {
        if (typeof(hookupCtrl) == "string")
        {
		    if (hookupCtrl != "")
		        useCtrlToValidate = true;
        }
    }
    // End V2.00 changes

    if (!useCtrlToValidate) {  // V2.00 change
        var ctrl_literal = dom_getAttribute(val, "valuetocompare");
        if (typeof(ctrl_literal) == "string") {
            compareTo = ctrl_literal;  // V2.00 change
         }
    }
    else {
        compareTo = ValidatorGetValue(dom_getAttribute(val, "controlhookup"));
    }
    operator = dom_getAttribute(val, "operator");
    return ValidatorCompare(value, compareTo, operator, val);
}

function CustomValidatorEvaluateIsValid(val) {
    var value = "";
    var ctrl = dom_getAttribute(val, "controltovalidate");
    if (typeof(ctrl) == "string") {
		if (ctrl != "") {
			value = ValidatorGetValue(ctrl);
			if (value == "")
				return true;
        }
    }
    var valid = true;
    var func_str = dom_getAttribute(val, "clientvalidationfunction");
    if (typeof(func_str) == "string") {
        if (func_str != "") {
            eval("valid = (" + func_str + "(val, value) != false);");
        }
    }
    return valid;
}

// Added for V2.0 changes - 27/1/2002 - Glav
//1 Feb 2005 added datatype range checking.
function RangeValidatorEvaluateIsValid(val) {
	var value;
    var ctrl = dom_getAttribute(val, "controltovalidate");
    if (typeof(ctrl) == "string") {
		if (ctrl != "") {
			value = ValidatorGetValue(ctrl);
			if (value == "")
				return true;
        }
    }

    var minval = dom_getAttribute(val,"minimumvalue");
    var maxval = dom_getAttribute(val,"maximumvalue");

	if (minval == null && maxval == null)
        return true;

    if (minval == "")
		minval = 0;
	if (maxval == "")
		maxval = 0;
	
	var dataType = dom_getAttribute(val, "type");

	if(dataType == null)
	    return ( (parseFloat(value) >= parseFloat(minval)) && (parseFloat(value) <= parseFloat(maxval)));

	//now do datatype range check.
	var min = ValidatorConvert(minval, dataType, val);
	var max = ValidatorConvert(maxval, dataType, val);
	value = ValidatorConvert(value, dataType, val);
	
	return value >= min && value <= max;
}

function ValidatorConvert(op, dataType, val) {
    function GetFullYear(year) {
        return (year + parseInt(dom_getAttribute(val,"century"))) - ((year < dom_getAttribute(val,"cutoffyear")) ? 0 : 100);
    }
    var num, cleanInput, m, exp;
    if (dataType == "Integer") {
        exp = /^\s*[-\+]?\d+\s*$/;
        if (op.match(exp) == null)
            return null;
        num = parseInt(op, 10);
        return (isNaN(num) ? null : num);
    }
    else if(dataType == "Double") {
        exp = new RegExp("^\\s*([-\\+])?(\\d+)?(\\" + val.decimalchar + "(\\d+))?\\s*$");
        m = op.match(exp);
        if (m == null)
            return null;
        cleanInput = m[1] + (m[2].length>0 ? m[2] : "0") + "." + m[4];
        num = parseFloat(cleanInput);
        return (isNaN(num) ? null : num);
    }
    else if (dataType == "Currency") {
        exp = new RegExp("^\\s*([-\\+])?(((\\d+)\\" + val.groupchar + ")*)(\\d+)"
                        + ((val.digits > 0) ? "(\\" + val.decimalchar + "(\\d{1," + val.digits + "}))?" : "")
                        + "\\s*$");
        m = op.match(exp);
        if (m == null)
            return null;
        var intermed = m[2] + m[5] ;
        cleanInput = m[1] + intermed.replace(new RegExp("(\\" + val.groupchar + ")", "g"), "") + ((val.digits > 0) ? "." + m[7] : 0);
        num = parseFloat(cleanInput);
        return (isNaN(num) ? null : num);
    }
    else if (dataType == "Date") {
		var fmt = dom_getAttribute(val, "dateformat");
		if(fmt != null)
			return prado_parseDate(op, fmt);

        var yearFirstExp = new RegExp("^\\s*((\\d{4})|(\\d{2}))([-./])(\\d{1,2})\\4(\\d{1,2})\\s*$");
        m = op.match(yearFirstExp);
        var day, month, year;
		var dateorder = dom_getAttribute(val, "dateorder");
        if (m != null && (m[2].length == 4 || dateorder == "ymd")) {
            day = m[6];
            month = m[5];
            year = (m[2].length == 4) ? m[2] : GetFullYear(parseInt(m[3], 10))
        }
        else {
            if (dateorder == "ymd"){
                return null;
            }
            var yearLastExp = new RegExp("^\\s*(\\d{1,2})([-./])(\\d{1,2})\\2((\\d{4})|(\\d{2}))\\s*$");
            m = op.match(yearLastExp);
            if (m == null) {
                return null;
            }
            if (dateorder == "mdy") {
                day = m[3];
                month = m[1];
            }
            else {
                day = m[1];
                month = m[3];
            }
            year = (m[5].length == 4) ? m[5] : GetFullYear(parseInt(m[6], 10))
        }
        month -= 1;
        var date = new Date(year, month, day);
        return (typeof(date) == "object" && year == date.getFullYear() && month == date.getMonth() && day == date.getDate()) ? date.valueOf() : null;
    }
    else {
        return op.toString();
    }
}

//1 Feb 2005 
//added a date parser
function prado_parseDate(str, fmt) 
{
	var y = 0;
	var m = -1;
	var d = 0;
	var a = str.split(/\W+/);

	var b = fmt.match(/%./g);
	var i = 0, j = 0;
	var hr = 0;
	var min = 0;
	for (i = 0; i < a.length; ++i) {
		if (!a[i])
			continue;
		switch (b[i]) {
		    case "%d":
		    case "%e":
			d = parseInt(a[i], 10);
			break;

		    case "%m":
			m = parseInt(a[i], 10) - 1;
			break;

		    case "%Y":
		    case "%y":
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
			break;

		    case "%H":
		    case "%I":
		    case "%k":
		    case "%l":
			hr = parseInt(a[i], 10);
			break;

		    case "%P":
		    case "%p":
			if (/pm/i.test(a[i]) && hr < 12)
				hr += 12;
			break;

		    case "%M":
			min = parseInt(a[i], 10);
			break;
		}
	}
	if (y != 0 && m != -1 && d != 0) {
		var date = new Date(y, m, d, hr, min, 0);
		return (typeof(date) == "object" && y == date.getFullYear() && m == date.getMonth() && d == date.getDate()) ? date.valueOf() : null;
	}
	y = 0; m = -1; d = 0;
	for (i = 0; i < a.length; ++i) {
		if (a[i].search(/[a-zA-Z]+/) != -1) {
			var t = -1;

			if (t != -1) {
				if (m != -1) {
					d = m+1;
				}
				m = t;
			}
		} else if (parseInt(a[i], 10) <= 12 && m == -1) {
			m = a[i]-1;
		} else if (parseInt(a[i], 10) > 31 && y == 0) {
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
		} else if (d == 0) {
			d = a[i];
		}
	}
	if (y == 0) {
		var today = new Date();
		y = today.getFullYear();
	}
	if (m != -1 && d != 0) {
		var date = new Date(y, m, d, hr, min, 0);
		return (typeof(date) == "object" && y == date.getFullYear() && m == date.getMonth() && d == date.getDate()) ? date.valueOf() : null;
	}
	return null;
}

function ValidationSummaryOnSubmit() {
    if (typeof(Page_ValidationSummaries) == "undefined")
        return;
    var summary, sums, s, summ_attrib, hdr_txt, err_msg;
    for (sums = 0; sums < Page_ValidationSummaries.length; sums++) {
        summary = Page_ValidationSummaries[sums];
        summary.style.display = "none";
        if (!Page_IsValid) {
			summ_attrib = dom_getAttribute(summary, "showsummary");
            if (summ_attrib != "False") {
                summary.style.display = "";
				var displaymode = dom_getAttribute(summary,"displaymode");
                if (typeof(displaymode) != "string") {
                    displaymode = "BulletList";
                }
                switch (displaymode) {
                    case "List":
                        headerSep = "<br>";
                        first = "";
                        pre = "";
                        post = "<br>";
                        final_block = "";
                        break;

                    case "BulletList":
                    default:
                        headerSep = "";
                        first = "<ul>";
                        pre = "<li>";
                        post = "</li>";
                        final_block = "</ul>";
                        break;

                    case "SingleParagraph":
                        headerSep = " ";
                        first = "";
                        pre = "";
                        post = " ";
                        final_block = "<br>";
                        break;
                }
                s = "";
                hdr_txt = dom_getAttribute(summary, "headertext");
                if (typeof(hdr_txt) == "string") {
                    s += hdr_txt + headerSep;
                }
                var cnt=0;
                s += first;
                for (i=0; i<Page_Validators.length; i++) {
                    err_msg = dom_getAttribute(Page_Validators[i], "errormessage");
                    if (!Page_Validators[i].isvalid && typeof(err_msg) == "string") {
						if (err_msg != "") {
							cnt++;
							s += pre + err_msg + post;
						}
                    }
                }
                s += final_block;

		// IE4 work around
                if (_val_IE4)
                {
					if (document.readyState == "complete")
					{
						summary.innerHTML  = s;
						window.scrollTo(0,0);
						summary.style.visibility = "visible";
					}
				} else
				{
					summary.innerHTML = s;
					window.scrollTo(0,0);
					summary.style.visibility = "visible";
				}
            }
            summ_attrib = dom_getAttribute(summary, "showmessagebox");

            if (summ_attrib == "True") {
                s = "";
                hdr_txt = dom_getAttribute(summary, "headertext");
                if (typeof(hdr_txt) == "string") {
                    s += hdr_txt + "\n";
                }
                for (i=0; i<Page_Validators.length; i++) {
					err_msg = dom_getAttribute(Page_Validators[i], "errormessage");
                    if (!Page_Validators[i].isvalid && typeof(err_msg) == "string") {
                        switch (displaymode) {
                            case "List":
                                s += err_msg + "\n";
                                break;

                            case "BulletList":
                            default:
                                s += "  - " + err_msg + "\n";
                                break;

                            case "SingleParagraph":
                                s += err_msg + " ";
                                break;
                        }
                    }
                }
                alert(s);
            }
        }
    }
}

////////////////////////--- Funtions to work in IE4 and DOM ---/////////////////////////////////

function dom_getAttribute(control,attribute)
{
	var attrib;
	if (_val_DOM)
		attrib = control.getAttribute(attribute, false);
	else
		attrib = eval(_val_allString + control.id + "." + attribute + _val_endAllString);
	return attrib;
}

function dom_getElementByID(id)
{
	var element = eval(_val_allString + id + _val_endAllString);
	return element;
}

function dom_getElementsByName(name)
{
	var elements = document.getElementsByName(name);
	return elements;
}

function pradoValidatorFocus(id)
{
	var obj = dom_getElementByID(id);
	if(obj != null && typeof(obj) != 'undefined' && typeof(obj.focus) != 'undefined')
	{
		window.setTimeout(function(){ obj.focus(); }, 100);

	}
}
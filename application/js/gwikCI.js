Array.prototype.in_array = function(p_val) 
{
	for(var i = 0, l = this.length; i < l; i++) {
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
}

function sel(id)
{
	return document.getElementById(id);
}

function val(id)
{
	return sel(id).value;
}

function submit(id)
{
	sel(id).submit();
}

function goto(adress)
{
	sel('form_goto').action = sel('form_goto').action+'/'+adress;
	submit('form_goto');
}

function hide(id)
{
	sel(id).style.display = 'none';
}


function show(id)
{
	sel(id).style.display = '';
}


function see(id)
{
	return sel(id).style.display != 'none';
}

function switchview(id)
{
	if (see(id)) hide(id);
	else show(id);
}

function hideA(L)
{
	var idA = new Array;
	idA = L.split(',');
	for (i = 0; i < idA.length; i++) hide(idA[i]);
}

function showA(L)
{
	var idA = new Array;
	idA = L.split(',');
	for (i = 0; i < idA.length; i++) show(idA[i]);
}

function Fval(id)
{
	if (sel(id).value == '') return 0;
	else return parseFloat(sel(id).value);
}

function opacity(obj,v)
{
	obj.style.opacity = v;
}

function handleEnter (field, event) {
	var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
	if (keyCode == 13) {
		var i;
		for (i = 0; i < field.form.elements.length; i++)
			if (field == field.form.elements[i])
				break;
		i = (i + 1) % field.form.elements.length;
		field.form.elements[i].focus();
		return false;
	} 
	else return true;
}   

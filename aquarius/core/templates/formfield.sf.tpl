{assign var="sf_options" value=$field.formfield->sup3|sf_split}
{html_options name=$field.formname options=$sf_options selected=$field.value}


<?php

/** Set this flag to ignore empty legends instead of deleting the present legend.
  * This helps against flaky servers that are not reliable enough to send out
  * the legend on newly selected pictures.
  *
  * The downside is that the legends cannot be deleted anymore when you enable
  * this.
  */
$config['global_legend_file']['ignore_empty'] = false;

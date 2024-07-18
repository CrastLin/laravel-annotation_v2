<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Controller
{
 /**
  * Used to mark a controller, only class annotations marked will take effect
  * The controller directory is located in the route configuration of the configuration file annotation.php
  */
}

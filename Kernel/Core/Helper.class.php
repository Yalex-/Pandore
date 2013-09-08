<?php

namespace Kernel\Core;

use Kernel\Services as Services;

/**
 * @brief This abstract class defines the minimal requirements for Helper objects.
 *
 * @see Kernel::Services::Tools.
 */
abstract class Helper
{
	use Services\Tools;
}

?>
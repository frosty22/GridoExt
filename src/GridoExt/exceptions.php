<?php

namespace GridoExt;

class LogicException extends \Exception { }
class InvalidCallException extends \GridoExt\LogicException { }
class UnexceptedMappingException extends \GridoExt\LogicException { }
class InvalidValueException extends \GridoExt\LogicException { }
class InvalidStateException extends \GridoExt\LogicException { }
class MissingServiceException extends \GridoExt\LogicException { }
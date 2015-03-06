<?php
namespace app\models\exceptions;

class PointUnderFlowException extends UnderFlowException
{
    protected $message = 'point under flow value.';
}

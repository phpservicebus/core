<?php
namespace PSB\Core\ErrorHandling\ErrorLastResort;


class ErrorLastResortHeaderTypeEnum
{
    const FAILED_QUEUE = 'PSB.ErrorLastResort.FailedQueue';
    const TIME_OF_FAILURE = 'PSB.ErrorLastResort.TimeOfFailure';
    const EXCEPTION_TYPE = 'PSB.Exception.Type';
    const EXCEPTION_MESSAGE = 'PSB.Exception.Message';
    const EXCEPTION_FILE = 'PSB.Exception.File';
    const EXCEPTION_TRACE = 'PSB.Exception.Trace';
    const PREV_EXCEPTION_TYPE = 'PSB.Exception.Previous.Type';
    const PREV_EXCEPTION_MESSAGE = 'PSB.Exception.Previous.Message';
    const PREV_EXCEPTION_FILE = 'PSB.Exception.Previous.File';
    const PREV_EXCEPTION_TRACE = 'PSB.Exception.Previous.Trace';
}

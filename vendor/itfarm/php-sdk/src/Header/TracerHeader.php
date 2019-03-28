<?php
/**
 * Created by PhpStorm.
 * User: Uasier
 * Date: 2018/12/13
 * Time: 9:38
 */

namespace ItFarm\PhpSdk\Header;

use whitemerry\phpkin\TracerInfo;

class TracerHeader extends DecorationHeader {
    /**
     * @var Header $header
     * @var array $extraArgs
     */
    private $header;
    private $extraArgs;

    /**
     * TracerHeader constructor.
     * @param Header $header
     * @param array $array
     */
    public function __construct(Header $header, $array = []){
        $this->header = $header;
        $this->extraArgs = $array;
    }
    public function getHeader()
    {
        return $this->header->getHeader();
    }
    public function addHeader(){
        return array_merge ($this->header->getHeader(), [
            'X-B3-NeedTracer' => true,
            'X-B3-TraceId' => TracerInfo::getTraceId(),
            'X-B3-SpanId' => (string) $this->extraArgs[0],
            'X-B3-ParentSpanId' => TracerInfo::getTraceSpanId(),
            'X-B3-Sampled' => ((int) TracerInfo::isSampled()),]);
    }

}
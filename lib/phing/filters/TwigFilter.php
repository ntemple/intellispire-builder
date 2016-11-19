<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ntemple
 * Date: 3/16/13
 * Time: 12:15 AM
 * To change this template use File | Settings | File Templates.
 */

require_once 'phing/filters/BaseFilterReader.php';
include_once 'phing/filters/ChainableReader.php';
require_once 'builder/TemplateEngine.php';


/**
 * Expands Phing Properties, if any, in the data.
 * <p>
 * Example:<br>
 * <pre><expandproperties/></pre>
 * Or:
 * <pre><filterreader classname="phing.filters.ExpandProperties'/></pre>
 *
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: d6bb7717db7cf2b122cbdcb93e5bb0f45d97ec52 $
 * @see       BaseFilterReader
 * @package   phing.filters
 */
class TwigFilter extends BaseFilterReader implements ChainableReader {

    protected $logLevel = Project::MSG_VERBOSE;

    /**
     * Set level of log messages generated (default = info)
     * @param string $level
     */
    public function setLevel($level)
    {
        switch ($level)
        {
            case "error": $this->logLevel = Project::MSG_ERR; break;
            case "warning": $this->logLevel = Project::MSG_WARN; break;
            case "info": $this->logLevel = Project::MSG_INFO; break;
            case "verbose": $this->logLevel = Project::MSG_VERBOSE; break;
            case "debug": $this->logLevel = Project::MSG_DEBUG; break;
        }
    }

    /**
     * Returns the filtered stream.
     * The original stream is first read in fully, and the Phing properties are expanded.
     *
     * @return mixed     the filtered stream, or -1 if the end of the resulting stream has been reached.
     *
     * @exception IOException if the underlying stream throws an IOException
     * during reading
     */
    function read($len = null) {

        $buffer = $this->in->read($len);

        if($buffer === -1) {
            return -1;
        }
        // $params = $this->getParameters();
        $ctx = new TemplateEngine();

        $project = $this->getProject();
//        $buffer = ProjectConfigurator::replaceProperties($project, $buffer, $project->getProperties(), $this->logLevel);


        $buffer = $ctx->render($buffer, $project->getProperties(), $this->logLevel);
        return $buffer;

    }

    /**
     * Creates a new ExpandProperties filter using the passed in
     * Reader for instantiation.
     *
     * @param object A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return object A new filter based on this configuration, but filtering
     *         the specified reader
     */
    function chain(Reader $reader) {
        $newFilter = new TwigFilter($reader);
        $newFilter->setProject($this->getProject());
        return $newFilter;
    }
}


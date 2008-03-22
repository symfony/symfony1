<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Doctrine_Template_Listener_Sluggable
 *
 * Easily create a slug for each record based on a specified set of fields
 *
 * @package     Doctrine
 * @subpackage  Template
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Template_Listener_Sluggable extends Doctrine_Record_Listener
{
    /**
     * Array of timestampable options
     *
     * @var string
     */
    protected $_options = array();

    /**
     * __construct
     *
     * @param string $array 
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    /**
     * preInsert
     *
     * @param Doctrine_Event $event 
     * @return void
     */
    public function preInsert(Doctrine_Event $event)
    {
        $name = $this->_options['name'];

        $record = $event->getInvoker();

        $record->$name = $this->buildSlug($record);
    }

    /**
     * preUpdate
     *
     * @param Doctrine_Event $event 
     * @return void
     */
    public function preUpdate(Doctrine_Event $event)
    {
        if (false !== $this->_options['unique']) {
            $name = $this->_options['name'];
    
            $record = $event->getInvoker();
    
            $record->$name = $this->buildSlug($record);        
        }
    }

    /**
     * buildSlug
     *
     * Generate the slug for a given Doctrine_Record
     *
     * @param Doctrine_Record $record 
     * @return string $slug
     */
    protected function buildSlug($record)
    {
        if (empty($this->_options['fields'])) {
            if (method_exists($record, 'getUniqueSlug')) {
                $value = $record->getUniqueSlug($record);
            } else {
                $value = (string) $record;
            }
        } else {
            if ($this->_options['unique'] === true) {   
                $value = $this->getUniqueSlug($record);
            } else {  
                $value = '';
                foreach ($this->_options['fields'] as $field) {
                    $value .= $record->$field . ' ';
                } 
            }

            $value =  Doctrine_Inflector::urlize($value);
        }

        return $value;
    }

    /**
     * getUniqueSlug
     *
     * Creates a unique slug for a given Doctrine_Record. This function enforces the uniqueness by incrementing
     * the values with a postfix if the slug is not unique
     *
     * @param Doctrine_Record $record 
     * @return string $slug
     */
    public function getUniqueSlug($record)
    {
        $name = $this->_options['name'];
        $slugFromFields = '';
        foreach ($this->_options['fields'] as $field) {
            $slugFromFields .= $record->$field . ' ';
        }

        $proposal = $record->$name ? $record->$name : $slugFromFields;
        $proposal =  Doctrine_Inflector::urlize($proposal);
        $slug = $proposal;

        $whereString = 'r.' . $name . ' LIKE ?';
        $whereParams = array($proposal.'%');
        
        if ($record->exists()) {
            $identifier = $record->identifier();
            $whereString .= ' AND r.' . implode(' != ? AND r.', $record->getTable()->getIdentifierColumnNames()) . ' != ?';
            $whereParams = array_merge($whereParams, array_values($identifier));
        }

        foreach ($this->_options['uniqueBy'] as $uniqueBy) {
            if (is_null($record->$uniqueBy)) {
                $whereString .= ' AND r.'.$uniqueBy.' IS NULL';
            } else {
                $whereString .= ' AND r.'.$uniqueBy.' = ?';
                $whereParams[] =  $record->$uniqueBy;
            }
        }

        $query = Doctrine_Query::create()
        ->select('r.'.$name)
        ->from(get_class($record).' r')
        ->where($whereString , $whereParams)
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

        $similarSlugResult = $query->execute();

        $similarSlugs = array();
        foreach ($similarSlugResult as $key => $value) {
            $similarSlugs[$key] = $value[$name];
        }

        $i = 1;
        while (in_array($slug, $similarSlugs)) {
            $slug = $proposal.'-'.$i;
            $i++;
        }

        return  $slug;
    }
}
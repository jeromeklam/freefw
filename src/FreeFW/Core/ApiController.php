<?php
namespace FreeFW\Core;

use \FreeFW\Constants as FFCST;

/**
 * Base controller
 *
 * @author jeromeklam
 */
class ApiController extends \FreeFW\Core\Controller
{

    /**
     * get Model by Id
     *
     * @param \FreeFW\Http\ApiParams $p_params
     * @param \FreeFW\Core\Model     $p_model
     * @param string                 $p_id
     *
     * @return NULL|\FreeFW\Model\ResultSet
     */
    protected function getModelById($p_params, $p_model, $p_id)
    {
        $filters  = new \FreeFW\Model\Conditions();
        $pk_field = $p_model->getPkField();
        $aField   = new \FreeFW\Model\ConditionMember();
        $aValue   = new \FreeFW\Model\ConditionValue();
        $aValue->setValue($p_id);
        $aField->setValue($pk_field);
        $aCondition = \FreeFW\Model\SimpleCondition::getNew();
        $aCondition->setLeftMember($aField);
        $aCondition->setOperator(\FreeFW\Storage\Storage::COND_EQUAL);
        $aCondition->setRightMember($aValue);
        $filters->add($aCondition);
        /**
         * @var \FreeFW\Model\Query $query
         */
        $query = $p_model->getQuery();
        $query
            ->addConditions($filters)
            ->addRelations($p_params->getInclude())
            ->setLimit(0, 1)
        ;
        $data = null;
        if ($query->execute()) {
            $data = $query->getResult();
        }
        return $data;
    }

    /**
     * AutoComplete
     *
     * @param \Psr\Http\Message\ServerRequestInterface $p_request
     * @param string $p_search
     *
     * @throws \FreeFW\Core\FreeFWStorageException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function autocomplete(\Psr\Http\Message\ServerRequestInterface $p_request, $p_search = '')
    {
        $this->logger->debug('FreeFW.ApiController.autocomplete.start');
        $data = [];
        /**
         * @var \FreeFW\Http\ApiParams $apiParams
         */
        $apiParams = $p_request->getAttribute('api_params', false);
        if (!isset($p_request->default_model)) {
            throw new \FreeFW\Core\FreeFWStorageException(
                sprintf('No default model for route !')
            );
        }
        $default = $p_request->default_model;
        $model   = \FreeFW\DI\DI::get($default);
        $fields  = $model->getAutocompleteField();
        $filters = [];
        if (is_array($fields)) {
            foreach ($fields as $oneField) {
                $filters[$oneField] = [\FreeFW\Storage\Storage::COND_LIKE => $p_search];
            }
        } else {
            $filters[$fields] = [\FreeFW\Storage\Storage::COND_LIKE => $p_search];
        }
        /**
         *
         * @var \FreeFW\Model\Query $query
         */
        $query = $model->getQuery();
        $query
            ->addFromFilters($filters)
            ->setOperator(\FreeFW\Storage\Storage::COND_OR)
            ->addRelations($apiParams->getInclude())
            ->setLimit(0, 30)
            ->setSort($apiParams->getSort())
        ;
        $data = new \FreeFW\Model\ResultSet();
        if ($query->execute()) {
            $data = $query->getResult();
        }
        $this->logger->debug('FreeFW.ApiController.autocomplete.end');
        return $this->createResponse(200, $data);
    }

    /**
     * Get children
     *
     * @param \Psr\Http\Message\ServerRequestInterface $p_request
     * @param number                                   $p_id
     *
     * @throws \FreeFW\Core\FreeFWStorageException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getChildren(\Psr\Http\Message\ServerRequestInterface $p_request, $p_id = null)
    {
        $this->logger->debug('FreeFW.ApiController.getChildren.start');
        /**
         * @var \FreeFW\Http\ApiParams $apiParams
         */
        $apiParams = $p_request->getAttribute('api_params', false);
        if (!isset($p_request->default_model)) {
            throw new \FreeFW\Core\FreeFWStorageException(
                sprintf('No default model for route !')
                );
        }
        $default = $p_request->default_model;
        $model   = \FreeFW\DI\DI::get($default);
        /**
         * Id
         */
        if ($p_id > 0) {
            $filters  = new \FreeFW\Model\Conditions();
            $pk_field = $model->getPkField();
            $aField   = new \FreeFW\Model\ConditionMember();
            $aValue   = new \FreeFW\Model\ConditionValue();
            $aValue->setValue($p_id);
            $aField->setValue($pk_field);
            $aCondition = \FreeFW\Model\SimpleCondition::getNew();
            $aCondition->setLeftMember($aField);
            $aCondition->setOperator(\FreeFW\Storage\Storage::COND_EQUAL);
            $aCondition->setRightMember($aValue);
            $filters->add($aCondition);
            /**
             * @var \FreeFW\Model\Query $query
             */
            $query = $model->getQuery();
            $query
                ->addConditions($filters)
                ->addRelations($apiParams->getInclude())
                ->setLimit(0, 1)
            ;
            $data = null;
            if ($query->execute()) {
                $data = $query->getResult();
            }
            $this->logger->debug('FreeFW.ApiController.getChildren.end');
            if (count($data) > 0) {
                $children = $model->find(
                    [
                        $model->getFieldNameByOption(FFCST::OPTION_NESTED_PARENT_ID) => $data[0]->getApiId()
                    ]
                );
                return $this->createResponse(200, $children);
            } else {
                return $this->createResponse(404);
            }
        } else {
            $children = $model->find(
                [
                    $model->getFieldNameByOption(FFCST::OPTION_NESTED_LEVEL) => 1
                ]
            );
            return $this->createResponse(200, $children);
        }
    }

    /**
     * Get all
     *
     * @param \Psr\Http\Message\ServerRequestInterface $p_request
     */
    public function getAll(\Psr\Http\Message\ServerRequestInterface $p_request)
    {
        $this->logger->debug('FreeFW.ApiController.getAll.start');
        /**
         * @var \FreeFW\Http\ApiParams $apiParams
         */
        $apiParams = $p_request->getAttribute('api_params', false);
        if (!isset($p_request->default_model)) {
            throw new \FreeFW\Core\FreeFWStorageException(
                sprintf('No default model for route !')
            );
        }
        $default = $p_request->default_model;
        $model   = \FreeFW\DI\DI::get($default);
        /**
         * @var \FreeFW\Model\Query $query
         */
        $query = $model->getQuery();
        $query
            ->addConditions($apiParams->getFilters())
            ->addRelations($apiParams->getInclude())
            ->setLimit($apiParams->getStart(), $apiParams->getlength())
            ->setSort($apiParams->getSort())
        ;
        $data = new \FreeFW\Model\ResultSet();
        if ($query->execute()) {
            $data = $query->getResult();
        }
        $this->logger->debug('FreeFW.ApiController.getAll.end');
        return $this->createResponse(200, $data);
    }

    /**
     * Get one by id
     *
     * @param \Psr\Http\Message\ServerRequestInterface $p_request
     */
    public function getOne(\Psr\Http\Message\ServerRequestInterface $p_request, $p_id = null)
    {
        $this->logger->debug('FreeFW.ApiController.getOne.start');
        /**
         * @var \FreeFW\Http\ApiParams $apiParams
         */
        $apiParams = $p_request->getAttribute('api_params', false);
        if (!isset($p_request->default_model)) {
            throw new \FreeFW\Core\FreeFWStorageException(
                sprintf('No default model for route !')
            );
        }
        $default = $p_request->default_model;
        $model   = \FreeFW\DI\DI::get($default);
        /**
         * Id
         */
        if ($p_id > 0) {
            $filters  = new \FreeFW\Model\Conditions();
            $pk_field = $model->getPkField();
            $aField   = new \FreeFW\Model\ConditionMember();
            $aValue   = new \FreeFW\Model\ConditionValue();
            $aValue->setValue($p_id);
            $aField->setValue($pk_field);
            $aCondition = \FreeFW\Model\SimpleCondition::getNew();
            $aCondition->setLeftMember($aField);
            $aCondition->setOperator(\FreeFW\Storage\Storage::COND_EQUAL);
            $aCondition->setRightMember($aValue);
            $filters->add($aCondition);
            /**
             * @var \FreeFW\Model\Query $query
             */
            $query = $model->getQuery();
            $query
                ->addConditions($filters)
                ->addRelations($apiParams->getInclude())
                ->setLimit(0, 1)
            ;
            $data = null;
            if ($query->execute()) {
                $data = $query->getResult();
            }
            $this->logger->debug('FreeFW.ApiController.getOne.end');
            if (count($data) > 0) {
                return $this->createResponse(200, $data[0]);
            } else {
                return $this->createResponse(404);
            }
        } else {
            if (method_exists($model, 'afterRead')) {
                $model->afterRead();
            }
            return $this->createResponse(200, $model);
        }
    }

    /**
     * Add new single element
     *
     * @param \Psr\Http\Message\ServerRequestInterface $p_request
     */
    public function createOne(\Psr\Http\Message\ServerRequestInterface $p_request)
    {
        $this->logger->debug('FreeFW.ApiController.createOne.start');
        $apiParams = $p_request->getAttribute('api_params', false);
        //
        if ($apiParams->hasData()) {
            /**
             * @var \FreeFW\Core\StorageModel $data
             */
            $data = $apiParams->getData();
            if (!$data->isValid()) {
                $this->logger->debug('FreeFW.ApiController.createOne.end');
                return $this->createResponse(409, $data);
            }
            $data->create();
            if (!$data->hasErrors()) {
                $data = $this->getModelById($apiParams, $data, $data->getApiId());
            }
            $this->logger->debug('FreeFW.ApiController.createOne.end');
            return $this->createResponse(201, $data);
        } else {
            return $this->createResponse(409);
        }
    }

    /**
     * Update single element
     *
     * @param \Psr\Http\Message\ServerRequestInterface $p_request
     */
    public function updateOne(\Psr\Http\Message\ServerRequestInterface $p_request, $p_id)
    {
        $this->logger->debug('FreeFW.ApiController.updateOne.start');
        $apiParams = $p_request->getAttribute('api_params', false);
        //
        if (intval($p_id) > 0 ) {
            if ($apiParams->hasData()) {
                /**
                 * @var \FreeFW\Core\StorageModel $data
                 */
                $data = $apiParams->getData();
                if ($data->isValid()) {
                    $data->save();
                    if (!$data->hasErrors()) {
                        $data = $this->getModelById($apiParams, $data, $data->getApiId());
                    }
                }
                $this->logger->debug('FreeFW.ApiController.updateOne.end');
                return $this->createResponse(200, $data);
            } else {
                return $this->createResponse(409, 'no data');
            }
        } else {
            return $this->createResponse(409, 'Id is mantarory');
        }
    }

    /**
     * Remove single element
     *
     * @param \Psr\Http\Message\ServerRequestInterface $p_request
     */
    public function removeOne(\Psr\Http\Message\ServerRequestInterface $p_request, $p_id = null)
    {
        $this->logger->debug('FreeFW.ApiController.removeOne.start');
        $apiParams = $p_request->getAttribute('api_params', false);
        if (!isset($p_request->default_model)) {
            throw new \FreeFW\Core\FreeFWStorageException(
                sprintf('No default model for route !')
            );
        }
        $default = $p_request->default_model;
        $model   = \FreeFW\DI\DI::get($default);
        //
        if (intval($p_id) > 0 ) {
            /**
             * @var \FreeFW\Core\StorageModel $data
             */
            $data = $model->findFirst([$model->getPkField() => $p_id]);
            if ($data) {
                if ($data->remove()) {
                    $this->logger->debug('FreeFW.ApiController.removeOne.end');
                    return $this->createResponse(204);
                } else {
                    return $this->createResponse(409, $data);
                }
            }
        }
        return $this->createResponse(409);
    }
}

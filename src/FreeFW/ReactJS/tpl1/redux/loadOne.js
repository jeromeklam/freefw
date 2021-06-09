import { freeAssoApi } from '../../../common';
import { jsonApiNormalizer, normalizedObjectModeler } from 'jsonapi-front';
import {
  [[:FEATURE_UPPER:]]_LOAD_ONE_BEGIN,
  [[:FEATURE_UPPER:]]_LOAD_ONE_SUCCESS,
  [[:FEATURE_UPPER:]]_LOAD_ONE_FAILURE,
  [[:FEATURE_UPPER:]]_LOAD_ONE_DISMISS_ERROR,
} from './constants';
import { getOne[[:FEATURE_CAMEL:]] } from '../';

export function loadOne(id = 0) {
  return dispatch => {
    dispatch({
      type: [[:FEATURE_UPPER:]]_LOAD_ONE_BEGIN,
    });
    const promise = new Promise((resolve, reject) => {
      const doRequest = getOne[[:FEATURE_CAMEL:]](id);
      doRequest.then(
        result => {
          const object = jsonApiNormalizer(result.data);
          const item   = normalizedObjectModeler(object, '[[:FEATURE_MODEL:]]', id, { eager: true } );
          dispatch({
            type: [[:FEATURE_UPPER:]]_LOAD_ONE_SUCCESS,
            data: result,
            item: item,
            id: id,
          });
          resolve(item);
        },
        err => {
          dispatch({
            type: [[:FEATURE_UPPER:]]_LOAD_ONE_FAILURE,
            data: { error: err },
            id: id,
          });
          reject(err);
        },
      );
    });
    return promise;
  };
}

export function dismissLoadOneError() {
  return {
    type: [[:FEATURE_UPPER:]]_LOAD_ONE_DISMISS_ERROR,
  };
}

export function reducer(state, action) {
  switch (action.type) {
    case [[:FEATURE_UPPER:]]_LOAD_ONE_BEGIN:
      // Just after a request is sent
      return {
        ...state,
        loadOnePending: true,
        loadOneError: null,
        createOneError: null,
        updateOneError: null,
      };

    case [[:FEATURE_UPPER:]]_LOAD_ONE_SUCCESS:
      // The request is success
      return {
        ...state,
        loadOnePending: false,
        loadOneItem: action.item,
        loadOneError: null,
      };

    case [[:FEATURE_UPPER:]]_LOAD_ONE_FAILURE:
      // The request is failed
      return {
        ...state,
        loadOnePending: false,
        loadOneError: action.data.error,
      };

    case [[:FEATURE_UPPER:]]_LOAD_ONE_DISMISS_ERROR:
      // Dismiss the request failure error
      return {
        ...state,
        loadOneError: null,
      };

    default:
      return state;
  }
}

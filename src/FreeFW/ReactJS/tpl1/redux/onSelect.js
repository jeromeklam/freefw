import { useCallback } from 'react';
import { useDispatch } from 'react-redux';
import {
  [[:FEATURE_UPPER:]]_ON_SELECT,
} from './constants';

export function onSelect(id) {
  return {
    type: [[:FEATURE_UPPER:]]_ON_SELECT,
    id: id,
  };
}

export function useOnSelect() {
  const dispatch = useDispatch();
  const boundAction = useCallback((...params) => dispatch(onSelect(...params)), [dispatch]);
  return { onSelect: boundAction };
}

export function reducer(state, action) {
  switch (action.type) {
    case [[:FEATURE_UPPER:]]_ON_SELECT:
      const { selected } = state;
      let filteredItems = [];
      const found = selected.find(elem => elem === action.id);
      if (found) {
        filteredItems = selected.filter(elem => elem !== action.id)
      } else {
        filteredItems = selected;
        filteredItems.push(action.id);
      }
      return {
        ...state,
        selected: filteredItems,
      };

    default:
      return state;
  }
}

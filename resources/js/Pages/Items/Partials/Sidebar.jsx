import RangeInput from '@/Components/Inputs/RangeInput.jsx';
import SelectInput from '@/Components/Inputs/SelectInput.jsx';
import TextInput from '@/Components/Inputs/TextInput.jsx';
import { Link, useForm } from '@inertiajs/react';
import { Button } from 'react-bootstrap';

export default function Sidebar({ categories }) {
  const params = new URLSearchParams(window.location.search);

  const { data, setData, get, errors, setError } = useForm({
    search: params.get('search') || '',
    category_id: params.get('category_id') || '',
    price_min: params.get('price_min') || '',
    price_max: params.get('price_max') || '',
  });

  function submit(e) {
    e.preventDefault();

    if (data.price_min && data.price_max && parseInt(data.price_min) > parseInt(data.price_max)) {
      setError('price_min', 'Min price must be less than max price');
      return;
    } else {
      setError('price_min', '');
    }

    get(route('items.items'));
  }

  return (
    <div className="z-0">
      <form onSubmit={submit}>
        <TextInput
          className="mb-3"
          label="Search"
          name="search"
          value={data.search}
          onChange={e => setData('search', e.target.value)}
        />

        <RangeInput
          className="mb-3"
          label="Price"
          nameMin="price_min"
          nameMax="price_max"
          valueMin={data.price_min}
          valueMax={data.price_max}
          onChangeMin={v => setData('price_min', v)}
          onChangeMax={v => setData('price_max', v)}
          error={errors.price_min}
          formControlPropsMin={{ min: 0, placeholder: 'Min' }}
          formControlPropsMax={{ min: 0, placeholder: 'Max' }}
        />

        <SelectInput
          className="mb-3"
          label="Category"
          name="category_id"
          value={data.category_id}
          onChange={v => setData('category_id', v)}
          error={errors.category_id}
          options={[{ id: '', name: 'All' }, ...categories]}
        />

        <div className="my-4 d-grid">
          <Button type="submit" variant="primary" size="lg">
            Search
          </Button>
        </div>

        <div className="my-4 d-grid">
          <Link href={route('items.create')} className="btn btn-success btn-block">
            List new item
          </Link>
        </div>
      </form>
    </div>
  );
}

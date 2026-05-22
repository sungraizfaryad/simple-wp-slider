import {
	DndContext,
	closestCenter,
	KeyboardSensor,
	PointerSensor,
	useSensor,
	useSensors,
} from '@dnd-kit/core';
import {
	arrayMove,
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { useSlider } from './SliderProvider';
import { SlideRow } from './SlideRow';

export function SlideList( { onEdit, onDelete } ) {
	const { state, dispatch } = useSlider();
	const sensors = useSensors(
		useSensor( PointerSensor ),
		useSensor( KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		} )
	);

	function onDragEnd( evt ) {
		const { active, over } = evt;
		if ( ! over || active.id === over.id ) {
			return;
		}
		const oldIdx = state.slides.findIndex( ( s ) => s.id === active.id );
		const newIdx = state.slides.findIndex( ( s ) => s.id === over.id );
		if ( oldIdx < 0 || newIdx < 0 ) {
			return;
		}
		dispatch( {
			type: 'set_slides',
			value: arrayMove( state.slides, oldIdx, newIdx ),
		} );
	}

	return (
		<DndContext
			sensors={ sensors }
			collisionDetection={ closestCenter }
			onDragEnd={ onDragEnd }
		>
			<SortableContext
				items={ state.slides.map( ( s ) => s.id ) }
				strategy={ verticalListSortingStrategy }
			>
				{ state.slides.map( ( slide ) => (
					<SlideRow
						key={ slide.id }
						slide={ slide }
						onEdit={ onEdit }
						onDelete={ onDelete }
					/>
				) ) }
			</SortableContext>
		</DndContext>
	);
}

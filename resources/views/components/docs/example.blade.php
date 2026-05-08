<li {{ $attributes->class([
    'bg-top-left bg-no-repeat list-none pl-8',
    'group-[.good]:bg-(image:--yes-icon)',
    'group-[.bad]:bg-(image:--no-icon)',
]) }}>{{ $slot }}</li>

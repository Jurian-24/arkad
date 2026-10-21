<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Arkad Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-sandstone p-8">

    <header class="mb-8">
        <h1 class="font-display text-4xl font-semibold text-sovereign">
            Wealth Overview
        </h1>

        <p class="mt-2 text-sm text-clay">
            An overview of your financial position.
        </p>
    </header>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-3">

        <x-ui.card>
            <p class="text-sm text-clay">Total Net Worth</p>

            <p class="mt-3 font-mono text-3xl font-semibold text-basalt">
                € 125.450
            </p>

            <x-ui.badge variant="yield" class="mt-4">
                +8.4%
            </x-ui.badge>
        </x-ui.card>

        <x-ui.card>
            <p class="text-sm text-clay">Monthly Savings</p>

            <p class="mt-3 font-mono text-3xl font-semibold text-basalt">
                € 2.400
            </p>

            <x-ui.badge variant="secure" class="mt-4">
                On Track
            </x-ui.badge>
        </x-ui.card>

        <x-ui.card>
            <p class="text-sm text-clay">Outstanding Liabilities</p>

            <p class="mt-3 font-mono text-3xl font-semibold text-basalt">
                € 12.500
            </p>

            <x-ui.badge variant="risk" class="mt-4">
                Review Needed
            </x-ui.badge>
        </x-ui.card>

    </section>

    <div class="mt-8 flex gap-3">
        <x-ui.button>
            Add Transaction
        </x-ui.button>

        <x-ui.button variant="secondary">
            Export Report
        </x-ui.button>
    </div>

</body>
</html>

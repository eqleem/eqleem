<div x-data="checkAll">
    <input x-show="$wire.allIdsOnPage.length" x-ref="checkbox" @change="handleCheck" type="checkbox" class="rounded-xl border-gray-300 shadow-sm w-4 h-4">
</div>

@script
    <script>
        Alpine.data('checkAll', () => {
            return {
                init() {
                    this.updateCheckAllState()

                    this.$watch('$wire.selectedIds', () => {
                        this.updateCheckAllState()
                    })

                    this.$watch('$wire.allIdsOnPage', () => {
                        this.updateCheckAllState()
                    })
                },

                updateCheckAllState() {
                    if (this.pageIsSelected()) {
                        this.$refs.checkbox.checked = true
                        this.$refs.checkbox.indeterminate = false
                    } else if (this.pageIsEmpty()) {
                        this.$refs.checkbox.checked = false
                        this.$refs.checkbox.indeterminate = false
                    } else {
                        this.$refs.checkbox.checked = false
                        this.$refs.checkbox.indeterminate = true
                    }
                },

                pageIsSelected() {
                    return this.$wire.allIdsOnPage.every(id => this.$wire.selectedIds.includes(id))
                },

                pageIsEmpty() {
                    return this.$wire.selectedIds.length === 0
                },

                handleCheck(e) {
                    e.target.checked ? this.selectAll() : this.deselectAll()
                },

                selectAll() {
                    this.$wire.allIdsOnPage.forEach(id => {
                        if (this.$wire.selectedIds.includes(id)) return

                        this.$wire.selectedIds.push(id)
                    })
                },

                deselectAll() {
                    this.$wire.selectedIds = []
                },
            }
        })
    </script>
@endscript

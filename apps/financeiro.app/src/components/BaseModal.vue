<template>
  <div
    class="modal fade"
    :id="id"
    tabindex="-1"
    :aria-labelledby="ariaTitleId"
    :data-bs-backdrop="backdropAttr"
    :data-bs-keyboard="keyboard ? 'true' : 'false'"
    @hidden.bs.modal="$emit('hidden')"
  >
    <div class="modal-dialog" :class="resolvedDialogClass">
      <div class="settings-widget">
        <div class="settings-inner-blk p-0">
          <div class="sell-course-head comman-space">
            <div class="modal-content" :class="contentClass">
              <div v-if="!$slots.header" class="modal-header">
                <h5 :id="ariaTitleId" class="modal-title">
                  <slot name="title">{{ title }}</slot>
                </h5>
                <button
                  type="button"
                  :id="closeButtonIdResolved"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Fechar"
                  @click.capture="moveFocusBeforeClose"
                />
              </div>
              <slot v-else name="header" />

              <template v-if="$slots.body">
                <slot name="body" />
              </template>
              <div v-else class="modal-body">
                <slot />
              </div>

              <div v-if="$slots.footer" class="modal-footer">
                <slot name="footer" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/** Classes de tamanho do `.modal-dialog` suportadas pelo Bootstrap 5. */
export const MODAL_DIALOG_CLASSES = Object.freeze([
  'modal-sm',
  'modal-md',
  'modal-lg',
  'modal-xl',
  'modal-fullscreen',
  'modal-fullscreen-sm-down',
  'modal-fullscreen-md-down',
  'modal-fullscreen-lg-down',
  'modal-fullscreen-xl-down',
  'modal-fullscreen-xxl-down'
])

function isValidDialogClass(value) {
  return MODAL_DIALOG_CLASSES.includes(value)
}

export default {
  name: 'BaseModal',
  props: {
    id: { type: String, required: true },
    title: { type: String, default: '' },
    staticBackdrop: { type: Boolean, default: true },
    keyboard: { type: Boolean, default: false },
    dialogClass: {
      type: String,
      default: 'modal-md',
      validator: (v) => isValidDialogClass(v)
    },
    contentClass: { type: [String, Array, Object], default: '' },
    titleId: { type: String, default: null },
    closeButtonId: { type: String, default: null }
  },
  emits: ['hidden'],
  computed: {
    resolvedDialogClass() {
      return isValidDialogClass(this.dialogClass) ? this.dialogClass : 'modal-md'
    },
    ariaTitleId() {
      return this.titleId || `${this.id}-title`
    },
    closeButtonIdResolved() {
      return this.closeButtonId || `${this.id}-close`
    },
    backdropAttr() {
      return this.staticBackdrop ? 'static' : undefined
    }
  },
  methods: {
    moveFocusBeforeClose() {
      if (document.activeElement && document.activeElement instanceof HTMLElement) {
        document.activeElement.blur()
      }
    }
  }
}
</script>

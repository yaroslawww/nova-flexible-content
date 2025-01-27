<template>
  <div
    :id="group.key"
    class="mb-4 w-full"
  >
    <div
      class="border border-gray-200 dark:border-gray-700 rounded-t-lg h-8 leading-normal flex items-center box-content"
      :class="{
        'rounded-b-lg': collapsed,
        'bg-red-100': groupHasErrors,
      }"
    >
      <BlockIconButton
        :icon="(collapsed || disabledExpand)?'plus':'minus'"
        :dusk="(collapsed || disabledExpand)?'expand-group':'collapse-group'"
        dusk=""
        class="border-r"
        :title="(collapsed || disabledExpand)?__('Expand'):__('Collapse')"
        :disabled="disabledExpand"
        :icon-class="{'opacity-50': disabledExpand}"
        @click.prevent="(collapsed || disabledExpand)?expand():collapse()"
      />
      <p class="flex-grow px-4 flex items-center overflow-hidden whitespace-nowrap truncate">
        <BlockIdText
          class="inline"
          :number="index + 1"
          :title="group.title"
        />
        <Badge
          v-if="descriptionText"
          class="ml-3 bg-primary-50 dark:bg-primary-500 text-primary-600 dark:text-gray-900 space-x-1 truncate"
        >
          {{ descriptionText }}
        </Badge>
      </p>
      <div
        v-if="!readonly"
        class="flex"
      >
        <BlockIconButton
          icon="selector"
          dusk="drag-group"
          class="border-l nova-flexible-content-drag-button"
          :title="__('Drag')"
        />
        <BlockIconButton
          icon="arrow-up"
          dusk="move-up-group"
          class="border-l"
          :title="__('Move up')"
          @click.prevent="moveUp"
        />
        <BlockIconButton
          icon="arrow-down"
          dusk="move-down-group"
          class="border-l"
          :title="__('Move down')"
          @click.prevent="moveDown"
        />
        <BlockIconButton
          icon="trash"
          dusk="delete-group"
          class="border-l"
          :title="__('Delete')"
          @click.prevent="confirmRemovingGroup"
        />
        <DeleteGroupModal
          v-if="displayRemoveConfirmation"
          :message="field.confirmRemoveMessage"
          :yes="field.confirmRemoveYes"
          :no="field.confirmRemoveNo"
          @confirm="remove"
          @close="displayRemoveConfirmation=false"
        />
      </div>
    </div>
    <div
      class="flex-grow border-b border-r border-l border-gray-200 dark:border-gray-700 rounded-b-lg"
      :class="{ 'hidden': collapsed }"
    >
      <component
        :is="'form-' + item.component"
        v-for="(item, index) in group.fields"
        :key="index"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :field="item"
        :errors="errors"
        :mode="mode"
        :show-help-text="item.helpText != null"
        :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
      />
    </div>
  </div>
</template>

<script>
import {find} from 'lodash';
import BehavesAsPanel from 'nova-mixins/BehavesAsPanel';
import {mapProps} from 'laravel-nova';
import DeleteGroupModal from '@/components/Modal/DeleteGroup.vue';
import BlockIconButton from '@/components/Block/IconButton.vue';
import BlockIdText from '@/components/Block/IdText.vue';

export default {

  components: {DeleteGroupModal, BlockIconButton, BlockIdText},
  mixins: [BehavesAsPanel],

  props: {
    errors: {},
    group: {},
    index: {},
    field: {},
    ...mapProps(['mode']),
  },

  emits: ['move-up', 'move-down', 'remove'],

  data() {
    return {
      displayRemoveConfirmation: false,
      readonly: this.group.readonly,
    };
  },

  computed: {
    disabledExpand() {
      return this.group.fields.length <= 0;
    },

    collapsed() {
      return this.group.collapsed || this.disabledExpand;
    },

    descriptionText() {
      if (this.group.configs.fieldUsedForDescription) {
        const groupSeparator = Nova.config('flexible-content-field.group-separator');

        const field = find(this.group.fields, {attribute: `${this.group.key}${groupSeparator}${this.group.configs.fieldUsedForDescription}`});
        if (field) {
          if (Array.isArray(field.options)) {
            const text = find(field.options, (option) => ((`${option?.value}`) === `${field.value}`))?.label;
            if (text !== undefined) {
              return text;
            }
          }
          return field?.value;
        }
      }

      return null;
    },

    groupHasErrors() {
      const recursiveKeysList = this.groupsRecursiveKeys(this.group);
      return !!Object.keys(this.errors.all()).find((key) => recursiveKeysList.some(groupKey => key.startsWith(groupKey)))
    }
  },

  methods: {
    /**
     * MFind group key and all child groups recursively.
     */
    groupsRecursiveKeys(group) {
      let keys = [group.key];

      if (Array.isArray(group.fields)) {
        group.fields.forEach(field => {
          if (field.component === 'flexible-content' && Array.isArray(field.value)) {
            field.value.forEach(value => {
              keys = keys.concat(this.groupsRecursiveKeys(value));
            });
          }
        })
      }
      if (Array.isArray(group.attributes)) {
        group.attributes.forEach(attribute => {
          if (attribute.component === 'flexible-content' && Array.isArray(attribute.value)) {
            attribute.value.forEach(value => {
              keys = keys.concat(this.groupsRecursiveKeys(value));
            });
          }
        })
      }

      return keys;
    },

    /**
     * Move this group up.
     */
    moveUp() {
      this.$emit('move-up');
    },

    /**
     * Move this group down
     */
    moveDown() {
      this.$emit('move-down');
    },

    /**
     * Remove this group
     */
    remove() {
      this.$emit('remove');
    },

    /**
     * Confirm remove message
     */
    confirmRemovingGroup() {
      if (this.field.confirmRemove) {
        this.displayRemoveConfirmation = true;
      } else {
        this.remove();
      }
    },

    /**
     * Expand fields
     */
    expand() {
      this.group.collapsed = false;
    },

    /**
     * Collapse fields
     */
    collapse() {
      this.group.collapsed = true;
    },
  },
};
</script>

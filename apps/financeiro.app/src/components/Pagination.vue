<template>
  <div class="row mt-3" v-if="_Pagination.Page">
    <div class="col-md-9">
      <nav aria-label="Paginação" v-if="_Pagination.TotalCount > 0">
        <ul class="pagination lms-page">
          <li class="page-item select-item">
            <MultselectForm
              :options="PaginationOp"
              v-model="pageSizeSel"
              label="name"
              placeholder=""
            />
          </li>
          <li class="page-item" @click="navPage('first')">
            <div class="page-link"><i class="fas fa-angle-left"></i><i class="fas fa-angle-left"></i></div>
          </li>
          <li class="page-item" @click="navPage('before')">
            <div class="page-link"><i class="fas fa-angle-left"></i></div>
          </li>
          <li
            v-for="nav in navPageRange"
            :key="nav.page"
            :class="'page-item pagination-item' + ((nav.page == Number(_Pagination.Page))? ' active' : '')"
            @click="navPage(nav.page)">
            <div class="page-link">{{ nav.page }}</div>
          </li>
          <li class="page-item page-range-item" disabled
              v-if=" (!navPageRange.map(i => {return i.page}).includes(_Pagination.PageRange))">
            <div class="page-link">...{{ _Pagination.PageRange }}</div>
          </li>
          <li class="page-item"
              @click="navPage('next')">
            <div class="page-link"><i class="fas fa-angle-right"></i></div>
          </li>
          <li class="page-item" @click="navPage('last')">
            <div class="page-link"><i class="fas fa-angle-right"></i><i class="fas fa-angle-right"></i></div>
          </li>
        </ul>
      </nav>
    </div>
    <div class="col-md-3">
      <p class="small fst-italic text-muted text-end" style="line-height: 34px" v-if="_Pagination.TotalCount > 0">
        <b>{{ (_Pagination.PageSize > _Pagination.TotalCount) ? _Pagination.TotalCount : _Pagination.PageSize }} listados
          de {{ _Pagination.TotalCount }} encontrados</b>
      </p>
    </div>
  </div>
  <div class="row" v-if="!_Pagination?.Page">
    <div class="col-md-12">
      <p class="text-muted">Paginação Indisponível</p>
    </div>
  </div>
</template>

<script>
import Helper from '../helpers/helper.service'

export default {
  name: "Pagination",
  props: {
    PaginationStoreName: null,
    Pagination: {
      Page: Number,
      PageSize: Number,
      PageRange: Number,
      TotalCount: Number,
    },
    disabled: {
      type: Boolean,
      default: false
    },
  },
  emits: [
    "pageUpdate",
  ],
  setup() {
    return {}
  },
  data() {
    return {
      isLoading: true,
      navPageRange: [],
      isMobile: false,
      rangeFactor: 1,
      _Pagination: {
        Page: null,
        PageSize: null,
        PageRange: null,
        TotalCount: null,
      },
      PaginationOp: [
        {size: 50, name: 'Listar 50'},
        {size: 100, name: 'Listar 100'},
        {size: 200, name: 'Listar 200'},
        {size: 400, name: 'Listar 400'},
      ],
      pageSizeSel: null,
    }
  },
  created() {

    if (this.PaginationStoreName && window.localStorage.getItem('pagination.' + this.PaginationStoreName)) {
      this.pageSizeSel = JSON.parse(window.localStorage.getItem('pagination.' + this.PaginationStoreName)) ?? this.PaginationOp[0]

    } else if (this.PaginationStoreName) {
      this.pageSizeSel = this.PaginationOp[0]
      window.localStorage.setItem('pagination.' + this.PaginationStoreName, JSON.stringify(this.pageSizeSel))
    }

    this._Pagination = this.Pagination
    this._Pagination.PageSize = this.pageSizeSel.size

    this.navPageRange = []
    this.isMobile = Helper.isMobile()
    this.rangeFactor = (this.isMobile) ? 1 : 9
  },
  mounted() {
    this.isLoading = false;
  },
  methods: {
    navPage(param = null) {
      let pagina = Number(this._Pagination.Page);

      switch (param) {
        case 'next':
          if (pagina < Number(this._Pagination.PageRange))
            pagina++
          break
        case 'before':
          if (pagina != 1)
            pagina--
          break
        case 'last':
          pagina = Number(this._Pagination.PageRange)
          break
        case 'first':
          pagina = 1
          break
        default:
          if (param) pagina = param
      }

      if (pagina != Number(this._Pagination.Page)) {
        this._Pagination.Page = pagina
        this.$emit('pageUpdate', this._Pagination)
      }
    },
    createPagination() {

      let maxRange = 1;
      if (this._Pagination.PageRange !== null)
        maxRange = (this._Pagination.PageRange <= this.rangeFactor) ? this._Pagination.PageRange : this.rangeFactor

      let starts = (this._Pagination.Page ?? 1) - ((maxRange - 1) / 2)
      if (starts < 1) {
        starts = 1
      } else if ((this._Pagination.PageRange != null) &&
        ((Number(this._Pagination.Page) + ((maxRange - 1) / 2)) > this._Pagination.PageRange)) {
        starts = this._Pagination.Page - (maxRange - 1 - (this._Pagination.PageRange - this._Pagination.Page))
      }

      let ends = maxRange
      if (Number(this._Pagination.Page) > (maxRange / 2))
        ends = Number(this._Pagination.Page) + ((maxRange - 1) / 2)

      if (this._Pagination.Range > ((maxRange - 1) / 2))
        ends = Number(this._Pagination.Page) + ((maxRange - 1) / 2)

      if (ends > Number(this._Pagination.PageRange)) ends = this._Pagination.PageRange

      let pages = [];
      for (let i = starts; i <= ends; i++) {
        pages.push({
          page: i
        });
      }

      this.navPageRange = pages;
    },
  },
  watch: {
    'pageSizeSel'(pageSize) {
      if (!this.isLoading) {
        this._Pagination.PageSize = pageSize.size;
        if (this.PaginationStoreName)
          window.localStorage.setItem('pagination.' + this.PaginationStoreName, JSON.stringify(pageSize))
        this.$emit('pageUpdate', this._Pagination)
      }
    },
    'Pagination.PageRange'() {
      this.createPagination();
    },
    Pagination: {
      handler() {
        this.createPagination();
      },
      deep: true,
    },
  },
}
</script>

<style scoped>

</style>

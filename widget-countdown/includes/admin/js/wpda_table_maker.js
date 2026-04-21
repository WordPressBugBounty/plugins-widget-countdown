class WpdaTableMaker {
  constructor(tableId = '') {
    if (!window.wpdaPageRowsList || !window.wpdaPageRowsInfo) {
      alert("Can't get Rows info. Please contact plugin authors.");
      return;
    }

    this.initialed = false;
    this.tableId = tableId;
    this.pageItemCount = 20;
    this.originalRowsList = window.wpdaPageRowsList;
    this.filteredRowsList = [...this.originalRowsList];
    this.pageRowsInfo = window.wpdaPageRowsInfo;
    this.itemCount = this.filteredRowsList.length;
    this.listObjectModel = {
      main: document.getElementById(this.tableId)
    };

    this.#init();
  }

  #init() {
    this.createNavigationHtml();
    this.createTableHtml();
    this.createTableHead();
    this.initialValues();
    this.filter();
    this.ordering();
    this.setOrderingCss();
    this.updatePagination();
    this.createTableBody();
    this.addEvents();
    this.initialed = true;
  }

  createHtmlElement(tag = '', attrs = {}, content = '') {
    const el = document.createElement(tag);
    for (const [key, val] of Object.entries(attrs)) {
      el.setAttribute(key, val);
    }
    if (content) el.innerHTML = content;
    return el;
  }

  getItemCountText() {
    const count = this.itemCount;
    return count === 0 ? 'no items' : `${count} item${count > 1 ? 's' : ''}`;
  }

  getOrderType(key) {
    for (const row of this.filteredRowsList) {
      if (isNaN(row[key])) return 'string';
    }
    return 'number';
  }

  initialValues() {
    const value = localStorage.getItem(this.pageRowsInfo.link_page + '_filter') || '';
    this.listObjectModel.searchInput.value = value;
  }

  filter() {
    const term = this.listObjectModel.searchInput.value.toLowerCase();
    this.filteredRowsList = this.originalRowsList.filter(row => {
      return Object.values(row).some(val => String(val).toLowerCase().includes(term));
    });
    this.itemCount = this.filteredRowsList.length;
    this.updatePagination(true);
  }

  ordering() {
    const orderBy = localStorage.getItem(this.pageRowsInfo.link_page + '_order_by');
    const order = localStorage.getItem(this.pageRowsInfo.link_page + '_order');
    if (!orderBy || !order) return;
    const type = this.getOrderType(orderBy);
    const compare = (a, b) => {
      let val1 = type === 'number' ? parseInt(a[orderBy]) : String(a[orderBy]).toLowerCase();
      let val2 = type === 'number' ? parseInt(b[orderBy]) : String(b[orderBy]).toLowerCase();
      if (val1 === val2) return 0;
      return (val1 > val2 ? 1 : -1) * (order === 'asc' ? 1 : -1);
    };
    this.filteredRowsList.sort(compare);
  }

  resetOrderingCss() {
    const ths = this.listObjectModel.thead.querySelectorAll('th.manage-column');
    ths.forEach(th => th.className = 'manage-column sortable desc');
  }

  setOrderingCss() {
    const orderBy = localStorage.getItem(this.pageRowsInfo.link_page + '_order_by');
    const order = localStorage.getItem(this.pageRowsInfo.link_page + '_order');
    const links = this.listObjectModel.link || [];
    for (const link of links) {
      if (link.getAttribute('date-key') === orderBy) {
        link.closest('th').className = 'manage-column sorted ' + order;
        break;
      }
    }
  }

  updatePagination(reset = false) {
    const pageKey = this.pageRowsInfo.link_page + '_current_page';
    let currentPage = parseInt(localStorage.getItem(pageKey)) || 1;
    const maxPage = Math.ceil(this.itemCount / this.pageItemCount);
    if (reset && this.initialed) {
      currentPage = 1;
      localStorage.setItem(pageKey, '1');
    }

    const model = this.listObjectModel;
    model.paginationPositionContainerCount.innerHTML = ' of ' + maxPage;
    model.navRowsCount.innerHTML = this.getItemCountText();
    model.paginationContainer.style.display = maxPage <= 1 ? 'none' : 'inline';

    const disable = (el, cond) => el && el.classList.toggle('disabled', cond);
    disable(model.paginationFirstPageLink, currentPage === 1);
    disable(model.paginationPreviousPageLink, currentPage === 1);
    disable(model.paginationNextPageLink, currentPage === maxPage);
    disable(model.paginationLastPageLink, currentPage === maxPage);

    model.paginationPositionContainerCurrent.value = currentPage;
  }

  createTableHtml() {
    const table = this.createHtmlElement('table', { class: 'wp-list-table widefat fixed pages' });
    const thead = this.createHtmlElement('thead', { class: 'tablenav top' });
    const tbody = this.createHtmlElement('tbody', { class: 'tablenav top' });
    table.append(thead, tbody);
    this.listObjectModel.table = table;
    this.listObjectModel.thead = thead;
    this.listObjectModel.tbody = tbody;
    this.listObjectModel.main.appendChild(table);
  }

  createTableHead() {
    const tr = this.createHtmlElement('tr');
    this.listObjectModel.link = [];
    for (const key in this.pageRowsInfo.keys) {
      const column = this.pageRowsInfo.keys[key];
      let th;
      if (column.sortable) {
        th = this.createHtmlElement('th', { class: 'manage-column sortable desc' });
        const a = this.createHtmlElement('a', { 'date-key': key });
        a.append(this.createHtmlElement('span', {}, column.name), this.createHtmlElement('span', { class: 'sorting-indicator' }));
        th.appendChild(a);
        this.listObjectModel.link.push(a);
      } else {
        th = this.createHtmlElement('th', { class: 'wpda-column-small' }, column.name);
      }
      tr.appendChild(th);
    }
    this.listObjectModel.thead.appendChild(tr);
  }

  createTableBody() {
    const tbody = this.listObjectModel.tbody;
    tbody.innerHTML = '';
    const currentPage = parseInt(localStorage.getItem(this.pageRowsInfo.link_page + '_current_page')) || 1;
    const start = (currentPage - 1) * this.pageItemCount;
    const end = Math.min(currentPage * this.pageItemCount, this.itemCount);

    for (let i = start; i < end; i++) {
      const row = this.filteredRowsList[i];
      const tr = this.createHtmlElement('tr');

      for (const key in this.pageRowsInfo.keys) {
        const column = this.pageRowsInfo.keys[key];
        const td = this.createHtmlElement('td');
        let value = row.hasOwnProperty(key) ? row[key] : column.name;

        if (column.replace_value && column.replace_value[value]) {
          value = column.replace_value[value];
        }

        const content = column.link
          ? this.createHtmlElement('a', { href: `admin.php?page=${this.pageRowsInfo.link_page}${column.link}&id=${row.id}` }, value)
          : this.createHtmlElement('span', {}, value);

        td.appendChild(content);
        tr.appendChild(td);
      }

      tbody.appendChild(tr);
    }
  }

  createNavigationHtml() {
    const model = this.listObjectModel;
    const mainNav = this.createHtmlElement('div', { class: 'tablenav top' });
    model.searchInput = this.createHtmlElement('input', { type: 'text', placeholder: 'Search', class: 'wpda_search' });
    model.navContainer = this.createHtmlElement('div', { class: 'tablenav-pages' });
    model.navRowsCount = this.createHtmlElement('span', { class: 'pageCount' }, this.getItemCountText());
    model.paginationContainer = this.createHtmlElement('span', { class: 'tablenav top' });

    const pagination = this.createHtmlElement('span', { class: 'pagination-links' });
    model.paginationFirstPageLink = this.createHtmlElement('a', { class: 'tablenav-pages-navspan button', title: 'Go to the first page' }, '«');
    model.paginationPreviousPageLink = this.createHtmlElement('a', { class: 'tablenav-pages-navspan button', title: 'Go to the previous page' }, '‹');
    const positionContainer = this.createHtmlElement('span', { class: 'tablenav top' });
    model.paginationPositionContainerCurrent = this.createHtmlElement('input', { type: 'text', class: 'current-page wpda-current-page', value: '1', size: '1' });
    model.paginationPositionContainerCount = this.createHtmlElement('span', {}, ' of ' + Math.ceil(this.itemCount / this.pageItemCount));
    model.paginationNextPageLink = this.createHtmlElement('a', { class: 'tablenav-pages-navspan button', title: 'Go to the next page' }, '›');
    model.paginationLastPageLink = this.createHtmlElement('a', { class: 'tablenav-pages-navspan button', title: 'Go to the last page' }, '»');

    positionContainer.append(model.paginationPositionContainerCurrent, model.paginationPositionContainerCount);
    pagination.append(model.paginationFirstPageLink, model.paginationPreviousPageLink, positionContainer, model.paginationNextPageLink, model.paginationLastPageLink);
    model.paginationContainer.appendChild(pagination);
    model.navContainer.append(model.navRowsCount, model.paginationContainer);
    mainNav.append(model.searchInput, model.navContainer);
    model.main.appendChild(mainNav);
  }

  addEvents() {
    const self = this;
    const model = this.listObjectModel;

    model.searchInput.addEventListener('input', function () {
      localStorage.setItem(self.pageRowsInfo.link_page + '_filter', this.value);
      self.filter();
      self.createTableBody();
    });

    model.link.forEach(link => {
      link.addEventListener('click', function () {
        const key = this.getAttribute('date-key');
        if (!self.pageRowsInfo.keys.hasOwnProperty(key)) return;

        self.resetOrderingCss();
        const pageKey = self.pageRowsInfo.link_page;
        const previousOrder = localStorage.getItem(pageKey + '_order');
        const previousKey = localStorage.getItem(pageKey + '_order_by');

        const order = (previousOrder === null || previousOrder === 'desc' || previousKey !== key) ? 'asc' : 'desc';
        localStorage.setItem(pageKey + '_order', order);
        localStorage.setItem(pageKey + '_order_by', key);

        self.ordering();
        self.setOrderingCss();
        self.createTableBody();
      });
    });

    const paginate = (page) => {
      localStorage.setItem(self.pageRowsInfo.link_page + '_current_page', String(page));
      self.updatePagination();
      self.createTableBody();
    };

    model.paginationFirstPageLink.addEventListener('click', () => paginate(1));
    model.paginationPreviousPageLink.addEventListener('click', () => {
      const current = parseInt(localStorage.getItem(self.pageRowsInfo.link_page + '_current_page')) || 1;
      paginate(Math.max(1, current - 1));
    });
    model.paginationNextPageLink.addEventListener('click', () => {
      const current = parseInt(localStorage.getItem(self.pageRowsInfo.link_page + '_current_page')) || 1;
      const max = Math.ceil(self.itemCount / self.pageItemCount);
      paginate(Math.min(max, current + 1));
    });
    model.paginationLastPageLink.addEventListener('click', () => {
      paginate(Math.ceil(self.itemCount / self.pageItemCount));
    });
    model.paginationPositionContainerCurrent.addEventListener('change', function () {
      const value = parseInt(this.value);
      if (!isNaN(value)) paginate(value);
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.wpdaTableList = new WpdaTableMaker('wpda_table_container');
});

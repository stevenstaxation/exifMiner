class endianReader {
  constructor(stream, pointer = 0, endian = 0) {
    // endian: 0 = little endian or 1 = big endian
    this.stream = stream;
    this.pointer = pointer;
    this.endian = endian;
  }

  readByte(length = 1) {
    let bytes = [];
    for (let ix = 0; ix < length; ix++) {
      bytes.push(this.stream[this.pointer]);
      this.pointer++;
    }
    return bytes;
  }

  readString(length = 1) {
    let string = "";
    for (let ix = 0; ix < length; ix++) {
      string += String.fromCharCode(this.stream[this.pointer]);
      this.pointer++;
    }
    return string;
  }

  readUInt16(length = 1) {
    let UINTs = [];
    if (this.endian == 1) {
      for (let ix = 0; ix < length; ix++) {
        UINTs.push(
          this.stream[this.pointer] * 256 + this.stream[this.pointer + 1]
        );
        this.pointer += 2;
      }
      return UINTs;
    } else {
      for (let ix = 0; ix < length; ix++) {
        UINTs.push(
          this.stream[this.pointer] + this.stream[this.pointer + 1] * 256
        );
        this.pointer += 2;
      }
      return UINTs;
    }
  }

  readUInt32(length = 1) {
    let UINTs = [];
    if (this.endian == 1) {
      for (let ix = 0; ix < length; ix++) {
        UINTs.push(
          this.stream[this.pointer] * Math.pow(256, 3) +
            this.stream[this.pointer + 1] * Math.pow(256, 2) +
            this.stream[this.pointer + 2] * Math.pow(256, 1) +
            this.stream[this.pointer + 3]
        );
        this.pointer += 4;
      }
      return UINTs;
    } else {
      for (let ix = 0; ix < length; ix++) {
        UINTs.push(
          this.stream[this.pointer + 3] * Math.pow(256, 3) +
            this.stream[this.pointer + 2] * Math.pow(256, 2) +
            this.stream[this.pointer + 1] * Math.pow(256, 1) +
            this.stream[this.pointer]
        );
        this.pointer += 4;
      }
      return UINTs;
    }
  }

  readTag(length = 1) {
    let items = [];
    for (let ix = 0; ix < length; ix++) {
      let item = [];
      item.tag = this.readUInt16();
      item.type = this.readUInt16();
      item.size = this.readUInt32();
      switch (Number(item.type)) {
        case 2:
          const strPointer = this.readUInt32();
          const savedPointer = this.currentPointer;
          this.currentPointer = strPointer;
          item.data = this.readString(item.size - 1);
          this.currentPointer = savedPointer;
          break;
        case 3:
          item.data = this.readUInt16();
          this.currentPointer += 2;
          break;
        case 4:
          item.data = this.readUInt32();
          break;
        default:
          item.data = this.readUInt32();
      }

      items.push(item);
    }
    return items;
  }

  get currentPointer() {
    return this.pointer;
  }

  set currentPointer(pointer) {
    this.pointer = pointer;
  }
}

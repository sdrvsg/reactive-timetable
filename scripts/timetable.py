import sys
import json
import datetime
import requests
import bs4


class Parser:
    def __init__(self):
        self.session = requests.Session()
        self.update_csrf_token()

    def update_csrf_token(self) -> None:
        response = self.session.get('https://ssau.ru/rasp')
        bs = bs4.BeautifulSoup(response.text, 'html.parser')
        token = bs.select_one('meta[name="csrf-token"]')['content']
        self.session.headers.update({'X-Csrf-Token': token})


    def get_group_id(self, query: str) -> int:
        self.session.headers.update({'Accept': 'application/json'})
        response = self.session.post('https://ssau.ru/rasp/search', data={'text': query})
        return response.json()[0]['id']


    def get_timetable_page(self, group_id: int, week: int = 1, day: int = 1) -> bs4.BeautifulSoup:
        self.session.headers.update({'Accept': '*/*'})
        response = self.session.get(f'https://ssau.ru/rasp?groupId={group_id}&selectedWeek={week}&selectedWeekday={day}')
        return bs4.BeautifulSoup(response.text, 'html.parser')


    def get_timetable(self, group_id: int, date: datetime) -> list:
        bs_start = self.get_timetable_page(group_id)
        date_start = datetime.datetime.strptime(bs_start.select_one('.schedule__head-date').text.strip(), '%d.%m.%Y')
        week = (date - date_start).days // 7 + 1
        day = (date - date_start).days % 7 + 1
        bs = self.get_timetable_page(group_id, week, day)

        pairs = []
        pair_types = {
            'Лекция': 'lecture',
            'Практика': 'practice',
            'Лабораторная': 'lab',
            'Другое': 'other',
        }

        pair_numbers = {
            '08:00' : 1,
            '09:45' : 2,
            '11:30' : 3,
            '13:30' : 4,
            '15:15' : 5,
            '17:00' : 6,
            '18:45' : 7,
            '20:25' : 8,
        }

        for pair, time in zip(bs.select('.schedule__item_show'), bs.select('.schedule__time')):
            data = {'number': pair_numbers[time.select_one('.schedule__time-item').text.strip()], 'is_present': False}
            if pair.text:
                delimiter = ' / '
                data['is_present'] = True
                data['type'] = pair_types.get(pair.select_one('.schedule__lesson-type-chip').text.strip(), 'other')
                data['name'] = delimiter.join(list(map(lambda p: p.text.strip(), pair.select('.schedule__discipline'))))
                data['place'] = delimiter.join(list(map(lambda p: p.text.strip(), pair.select('.schedule__place'))))
                data['teacher'] = delimiter.join(list(map(lambda p: p.text.strip(), pair.select('.schedule__teacher'))))
                data['groups'] = delimiter.join(list(map(lambda p: p.text.strip().split('-')[0], pair.select('.schedule__groups .caption-text'))))
            pairs.append(data)

        exists = [n['number'] for n in pairs]
        for number in range(1, 9):
            if number not in exists:
                pairs.append({'number': number, 'is_present': False})
        return pairs


def main():
    try:
        parser = Parser()
        print(json.dumps({
            'success': True,
            'data': parser.get_timetable(
                parser.get_group_id(sys.argv[1]),
                datetime.datetime.strptime(sys.argv[2], '%d.%m.%Y')
            )
        }))
    except Exception as e:
        print(json.dumps({'success': False, 'data': [], 'message': str(e)}))


if __name__ == '__main__':
    main()
